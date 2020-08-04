<?php

namespace  app\modules\bot\controllers\publics;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\models\User;
use app\modules\bot\models\RatingVote;
use app\modules\bot\models\RatingVoting;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use app\modules\bot\components\helpers\Emoji;

class TopController extends Controller
{
    const VOTE_LIKE = 1;
    const VOTE_DISLIKE = -1;
    const LIKE_MESSAGE = '+';
    const DISLIKE_MESSAGE = '-';

    public function beforeAction($action)
    {
        $chat = $this->getTelegramChat();

        $isBotAdmin = false;

        $botUser = User::find()
            ->where([
                'provider_user_name' => $this->getBotName(),
            ])
            ->one();

        if ($botUser) {
            $isBotAdmin = ChatMember::find()
                ->where([
                    'chat_id' => $chat->id,
                    'user_id' => $botUser->id,
                    'status' => ChatMember::STATUS_ADMINISTRATOR,
                ])
                ->exists();
        }

        $starTopStatus = $chat->getSetting(ChatSetting::STAR_TOP_STATUS);

        if (!$isBotAdmin || !parent::beforeAction($action) || !isset($starTopStatus) || ($starTopStatus->value != ChatSetting::STAR_TOP_STATUS_ON)) {
            return false;
        }

        return true;
    }

    public function actionIndex()
    {
        $chat = $this->getTelegramChat();

        $users = RatingVote::find()
            ->select([
                'provider_user_id' => 'provider_candidate_id',
                'rating' => 'sum(vote)',
            ])
            ->where(['chat_id' => $chat->id])
            ->groupBy('provider_candidate_id')
            ->orderBy(['rating' => SORT_DESC])
            ->having(['>', 'rating', 0])
            ->asArray()
            ->all();

        if (!$users) {
            return [];
        }

        return $this->getResponseBuilder()
            ->sendMessage(
                $this->render('index', [
                    'users' => $users,
                ]),
                [],
                false,
                [
                    'replyToMessageId' => $this->getMessage()->getMessageId(),
                ]
            )
            ->build();
    }

    public function actionStartLike()
    {
        return $this->actionStart(self::LIKE_MESSAGE);
    }

    public function actionStartDislike()
    {
        return $this->actionStart(self::DISLIKE_MESSAGE);
    }

    public function actionStart($estimate)
    {
        if ($estimate == self::LIKE_MESSAGE) {
            $estimateValue = self::VOTE_LIKE;
        } elseif ($estimate == self::DISLIKE_MESSAGE) {
            $estimateValue = self::VOTE_DISLIKE;
        }

        $message = $this->getMessage();
        $messageId = $message->getMessageId();
        $telegramUser = $this->getTelegramUser();
        $chat = $this->getTelegramChat();
        $estimatedMessage = $message->getReplyToMessage();
        $estimatedMessageId = $estimatedMessage->getMessageId();

        $canCreateVoting = isset($estimateValue) && $estimatedMessage && !$estimatedMessage->getFrom()->isBot() && ($telegramUser->provider_user_id != $estimatedMessage->getFrom()->getId());
        if (!$canCreateVoting) {
            return [];
        }

        $this->addOrChangeVote($estimatedMessageId, $estimateValue);

        $resultCommand = $this->getResultCommand($estimatedMessageId);
        $sendMessageCommand = array_pop($resultCommand);
        $sendMessageCommand->replyToMessageId = $estimatedMessageId;
        $votingMessage = $sendMessageCommand->send($this->getBotApi());

        $votings = RatingVoting::find()
            ->where([
                'candidate_message_id' => $estimatedMessageId,
                'chat_id' => $chat->id,
            ])
            ->all();

        if ($votings) {
            foreach ($votings as $voting) {
                try {
                    $this->getBotApi()->deleteMessage($chat->chat_id, $voting->voting_message_id);
                } catch (HttpException $e) {
                    echo 'ERROR: Public ' . $this->id . '/' . $this->action->id . ' MessageId ' . $voting->voting_message_id . ' (deleteMessage): ' . $e->getMessage() . "\n";
                }

                $voting->delete();
            }
        }

        $voting = new RatingVoting();
        if ($votingMessage) {
            $voting->voting_message_id = $votingMessage->getMessageId();
            $voting->candidate_message_id = $estimatedMessageId;
            $voting->provider_starter_id = $telegramUser->provider_user_id;
            $voting->chat_id = $chat->id;
            $voting->command = $this->getMessage()->getText();
            $voting->save();
        }

        try {
            $this->getBotApi()->deleteMessage($chat->chat_id, $messageId);
        } catch (HttpException $e) {
            echo 'ERROR: Public ' . $this->id . '/' . $this->action->id . ' MessageId ' . $messageId . ' (deleteMessage): ' . $e->getMessage() . "\n";
        }

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
        $chat = $this->getTelegramChat();
        $user = $this->getTelegramUser();

        return RatingVote::find()
            ->where([
                'message_id' => $messageId,
                'chat_id' => $chat->id,
                'provider_candidate_id' => $user->provider_user_id
            ])
            ->exists();
    }

    private function addOrChangeVote(int $messageId, int $estimate)
    {
        $chat = $this->getTelegramChat();
        $chatId = $chat->id;
        $user = $this->getTelegramUser();
        $voterId = $user->provider_user_id;
        $thisMessage = $this->getMessage();

        if ($this->getUpdate()->getCallbackQuery()) {
            $anyMessageVote = RatingVote::find()
                ->where([
                    'message_id' => $messageId,
                    'chat_id' => $chatId
                ])
                ->one();

            $candidateId = $anyMessageVote->provider_candidate_id;
        } else {
            $estimatedMessage = $thisMessage->getReplyToMessage();
            $candidateId = $estimatedMessage->getFrom()->getId();
        }

        $vote = RatingVote::find()
            ->where([
                'message_id' => $messageId,
                'chat_id' => $chatId,
                'provider_voter_id' => $voterId
            ])
            ->one();

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

        $voting = RatingVoting::find()
            ->where([
                'chat_id' => $chat->id,
                'candidate_message_id' => $messageId,
            ])
            ->one();

        $likeVotes = RatingVote::find()
            ->where([
                'message_id' => $messageId,
                'chat_id' => $chat->id,
                'vote' => self::VOTE_LIKE,
            ])
            ->count();

        $dislikeVotes = RatingVote::find()
            ->where([
                'message_id' => $messageId,
                'chat_id' => $chat->id,
                'vote' => self::VOTE_DISLIKE,
            ])
            ->count();

        // TODO add provider_candidate_id to RatingVoting
        $voteToGetCandidate = RatingVote::find()
            ->where([
                'message_id' => $messageId,
                'chat_id' => $chat->id,
            ])
            ->one();

        $candidateId = $voteToGetCandidate->provider_candidate_id;

        $buttons = [
                [
                    [
                        'callback_data' => self::createRoute('like-message', ['messageId' => $messageId]),
                        'text' => Emoji::LIKE . ( $likeVotes != 0 ? ' ' . $likeVotes : ''),
                    ],
                    [
                        'callback_data' => self::createRoute('dislike-message', ['messageId' => $messageId]),
                        'text' => Emoji::DISLIKE . ( $dislikeVotes != 0 ? ' ' . $dislikeVotes : ''),
                    ],
                ]
            ];

        if ($this->getUpdate()->getCallbackQuery()) {
            $voterId = $voting->provider_starter_id;
        } else {
            $voterId = $this->getMessage()->getFrom()->getId();
        }

        $ratings = ArrayHelper::map(
            RatingVote::find()
                ->where([
                    'provider_candidate_id' => [
                        $voterId,
                        $candidateId,
                    ],
                    'chat_id' => $chat->id,
                ])
                ->groupBy('provider_candidate_id')
                ->select([
                    'provider_candidate_id',
                    'rating' => 'sum(vote)'
                ])
                ->asArray()
                ->all(),
                'provider_candidate_id',
                'rating'
        );

        return [];

        $commands = $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render(
                    'show-voting',
                    [
                        'providerVoterId' => $voterId,
                        'providerCandidateId' => $candidateId,
                        'userRating' => $ratings[$voterId] ?? 0,
                        'candidateRating' => $ratings[$candidateId] ?? 0,
                        'command' => $voting->command,
                    ]
                ),
                $buttons
            )
            ->build();

        return $commands;
    }
}
