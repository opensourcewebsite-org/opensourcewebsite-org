<?php

namespace  app\modules\bot\controllers\publics;

use app\modules\bot\components\Controller;
use app\modules\bot\components\response\commands\DeleteMessageCommand;
use app\modules\bot\components\response\commands\EditMessageTextCommand;
use app\modules\bot\components\response\ResponseBuilder;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\models\User;
use app\modules\bot\models\RatingVote;
use app\modules\bot\models\RatingVoting;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use TelegramBot\Api\Types\Message;
use Yii;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;

class TopController extends Controller
{
    const VOTE_LIKE = 1;
    const VOTE_DISLIKE = -1;

    public function beforeAction($action)
    {
        $chat = $this->getTelegramChat();
        $isBotAdmin = false;
        $botUser = User::find()->where(['provider_user_name' => $this->getBotName()])->one();
        if ($botUser) {
            $isBotAdmin = ChatMember::find()->where(['chat_id' => $chat->id, 'user_id' => $botUser->id, 'status' => ChatMember::STATUS_ADMINISTRATOR])->exists();
        }

        $starTopStatus = $chat->getSetting(ChatSetting::STAR_TOP_STATUS);

        if (($action->id != 'index') && (!$isBotAdmin || !parent::beforeAction($action) || !isset($starTopStatus) || ($starTopStatus->value != ChatSetting::STAR_TOP_STATUS_ON))) {
            return false;
        }
        return true;
    }

    public function actionIndex()
    {
        $chat = $this->getTelegramChat();
        $tops = RatingVote::find()
        ->select(['provider_candidate_id', 'rating' => 'sum(vote)'])
        ->where(['chat_id' => $chat->id])
        ->groupBy('provider_candidate_id')
        ->orderBy(['rating' => SORT_DESC])
        ->having(['>', 'rating', 0])
        ->asArray()
        ->all();

        if (!$tops) {
            return [];
        }

        foreach ($tops as &$top) {
            $top['username'] = $this->getProviderUsernameById($top['provider_candidate_id']);
        }
        return $this->getResponseBuilder()->sendMessage(
            $this->render('index', [
                'users' => $tops
            ]),
            [],
            false,
            [
                'replyToMessageId' => $this->getMessage()->getMessageId()
            ]
            )->build();
    }

    public function actionStart($estimate)
    {
        if ($estimate == '+') {
            $estimateValue = self::VOTE_LIKE;
        } elseif ($estimate == '-') {
            $estimateValue = self::VOTE_DISLIKE;
        }

        $update = $this->getUpdate();

        $message = $this->getMessage();
        $messageId = $message->getMessageId();
        $currentUser = $this->getTelegramUser();
        $chat = $this->getTelegramChat();
        $estimatedMessage = $message->getReplyToMessage();
        $estimatedMessageId = $estimatedMessage->getMessageId();

        $canCreateVoting = isset($estimateValue) && $estimatedMessage && !$estimatedMessage->getFrom()->isBot() && ($currentUser->provider_user_id != $estimatedMessage->getFrom()->getId());
        if (!$canCreateVoting) {
            return [];
        }

        $this->addOrChangeVote($estimatedMessageId, $estimateValue);

        $resultCommand = $this->getResultCommand($estimatedMessageId);
        $sendMessageCommand = array_pop($resultCommand);
        $sendMessageCommand->replyToMessageId = $estimatedMessageId;
        $votingMessage = $sendMessageCommand->send($this->getBotApi());

        $votings = RatingVoting::find()->where(['candidate_message_id' => $estimatedMessageId, 'chat_id' => $chat->id])->all();
        if ($votings) {
            foreach ($votings as $voting) {
                $deleteMessageCommand = new DeleteMessageCommand($chat->chat_id, $voting->voting_message_id);
                $deleteMessageCommand->send($this->getBotApi());
                $voting->delete();
            }
        }

        $voting = new RatingVoting();
        if ($votingMessage) {
            $voting->voting_message_id = $votingMessage->getMessageId();
            $voting->candidate_message_id = $estimatedMessageId;
            $voting->provider_starter_id = $currentUser->provider_user_id;
            $voting->chat_id = $chat->id;
            $voting->save();
        }

        $deleteMessageCommand = new DeleteMessageCommand($chat->chat_id, $messageId);
        $deleteMessageCommand->send($this->getBotApi());
        return [];
    }

    public function actionLikeMessage($messageId)
    {
        if ($this->isItUserSelfVote($messageId)) {
            return [];
        }
        $this->addOrChangeVote($messageId, self::VOTE_LIKE);
        return $this->getResultCommand($messageId);
    }

    public function actionDislikeMessage($messageId)
    {
        if ($this->isItUserSelfVote($messageId)) {
            return [];
        }
        $this->addOrChangeVote($messageId, self::VOTE_DISLIKE);
        return $this->getResultCommand($messageId);
    }

    private function isItUserSelfVote($messageId)
    {
        $chatId = $this->getTelegramChat()->id;
        $user = $this->getTelegramUser();
        return RatingVote::find()->where(['message_id' => $messageId, 'chat_id' => $chatId, 'provider_candidate_id' => $user->provider_user_id])->exists();
    }

    private function addOrChangeVote(int $messageId, int $estimate)
    {
        $chat = $this->getTelegramChat();
        $chatId = $chat->id;
        $user = $this->getTelegramUser();
        $voterId = $user->provider_user_id;
        $thisMessage = $this->getMessage();

        if ($this->getUpdate()->getCallbackQuery()) {
            $anyMessageVote = RatingVote::find()->where(['message_id' => $messageId, 'chat_id' => $chatId])->one();
            $candidateId = $anyMessageVote->provider_candidate_id;
        } else {
            $estimatedMessage = $thisMessage->getReplyToMessage();
            $candidateId = $estimatedMessage->getFrom()->getId();
        }

        $vote = RatingVote::find()->where(['message_id' => $messageId, 'chat_id' => $chatId, 'provider_voter_id' => $voterId])->one();
        if (!$vote) {
            $vote = new RatingVote();
            $vote->message_id = $messageId;
            $vote->provider_voter_id = $voterId;
            $vote->provider_candidate_id = $candidateId;
            $vote->chat_id = $chat->id;
        }
        if ($vote->vote != $estimate) {
            $vote->vote = $estimate;
            $vote->save();
        }
        return;
    }

    private function getResultCommand($messageId)
    {
        $chat = $this->getTelegramChat();
        $chatId = $chat->id;

        $likeVotes = RatingVote::find()->where(['message_id' => $messageId, 'chat_id' => $chatId, 'vote' => self::VOTE_LIKE])->count();
        $dislikeVotes = RatingVote::find()->where(['message_id' => $messageId, 'chat_id' => $chatId, 'vote' => self::VOTE_DISLIKE])->count();

        $voteToGetCandidate = RatingVote::find()->where(['message_id' => $messageId, 'chat_id' => $chatId])->one();
        $candidateId = $voteToGetCandidate->provider_candidate_id;

        $candidate = $this->getProviderUsernameById($candidateId);
        $replyMarkup = [
                [
                    [
                        'callback_data' => self::createRoute('like-message', ['messageId' => $messageId]),
                        'text' => '👍' . ( $likeVotes !=0 ? ' ' . $likeVotes : ''),
                    ],
                    [
                        'callback_data' => self::createRoute('dislike-message', ['messageId' => $messageId]),
                        'text' => '👎' . ( $dislikeVotes !=0 ? ' ' . $dislikeVotes : ''),
                    ],
                ]
            ];

        if ($this->getUpdate()->getCallbackQuery()) {
            $voting = RatingVoting::find()->where(['chat_id' => $chat->id, 'candidate_message_id' => $messageId])->one();
            $voterId = $voting->provider_starter_id;
        } else {
            $voterId = $this->getMessage()->getFrom()->getId();
        }

        $ratings = ArrayHelper::map(
            RatingVote::find()
            ->where(['provider_candidate_id' => [$voterId,$candidateId], 'chat_id' => $chat->id])
            ->groupBy('provider_candidate_id')
            ->select(['provider_candidate_id', 'rating' => 'sum(vote)'])
            ->asArray()
            ->all(),
            'provider_candidate_id',
            'rating'
        );

        $voterName = $this->getProviderUsernameById($voterId);
        $commands = $this->getResponseBuilder()->editMessageTextOrSendMessage(
            $this->render(
                'vote',
                [
                    'voter' => $voterName,
                    'candidate' => $candidate,
                    'userRating' => $ratings[$voterId] ?? 0,
                    'candidateRating' => $ratings[$candidateId] ?? 0,
                ]
            ),
            $replyMarkup
        )->build();

        return $commands;
    }

    private function getProviderUsernameById($userId)
    {
        try {
            $user = $this->getBotApi()->getChatMember(
                $this->getTelegramChat()->chat_id,
                $userId
            )->getUser();
            $nickname = $user->getUsername();
            $username = $nickname ? '@' . $nickname : Html::a(implode(' ', [$user->getFirstName(), $user->getLastName()]), 'tg://user?id=' . $userId);
            return $username;
        } catch (HttpException $e) {
            return '';
        }
    }
}
