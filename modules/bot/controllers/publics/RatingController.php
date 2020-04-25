<?php

namespace  app\modules\bot\controllers\publics;

use app\modules\bot\components\Controller;
use app\modules\bot\components\response\commands\DeleteMessageCommand;
use app\modules\bot\components\response\ResponseBuilder;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\models\User;
use app\modules\bot\models\RatingVote;
use app\modules\bot\models\RatingVoting;
use Yii;
use TelegramBot\Api\Types\Message;

/**
 *
 */
class RatingController extends Controller
{
    const VOTE_LIKE = 1;
    const VOTE_DISLIKE = -1;

    public function beforeAction($action)
    {
        $chat = $this->getTelegramChat();
        $chatId = $chat->chat_id;

        $isBotAdmin = false;
        $botUser = User::find()->where(['provider_user_name' => $this->getBotName()])->one();
        if ($botUser) {
            $isBotAdmin = ChatMember::find()->where(['chat_id' => $chat->id, 'user_id' => $botUser->id, 'status' => ChatMember::STATUS_ADMINISTRATOR])->exists();
        }

        $starTopStatus = $chat->getSetting(ChatSetting::STAR_TOP_STATUS)->value;
        $isStarTopOff = ($starTopStatus != ChatSetting::STAR_TOP_STATUS_ON);

        if (!$isBotAdmin || !parent::beforeAction($action) || $isStarTopOff) {
            return false;
        }
        return true;
    }

    public function actionIndex($estimate)
    {
        if ($estimate == '+') {
            $estimateValue = self::VOTE_LIKE;
        } elseif ($estimate == '-') {
            $estimateValue = self::VOTE_DISLIKE;
        }

        $update = $this->getUpdate();
        $message = $update->getMessage();
        $messageId = $message->getMessageId();
        $chat = $this->getTelegramChat();
        $chatId = $chat->chat_id;
        $estimatedMessage = $message->getReplyToMessage();
        $estimatedMessageId = $estimatedMessage->getMessageId();

        $canCreateVoting = isset($estimateValue) && $estimatedMessage;
        if (!$canCreateVoting) {
            return [];
        }
        $this->AddOrChangeVote($estimatedMessageId, $estimateValue);

        $sendMessageCommand = array_pop($this->getResultCommand($estimatedMessageId));
        $sendMessageCommand->replyToMessageId = $estimatedMessageId;
        $votingMessage = $sendMessageCommand->send($this->getBotApi());

        $voting = new RatingVoting();
        $voting->voting_message_id = $votingMessage->getMessageId();
        $voting->candidate_message_id = $estimatedMessageId;
        $voting->chat_id = $chat->id;
        $voting->save();

        return [];
    }


    public function actionLikeMessage($messageId)
    {
        $this->AddOrChangeVote($messageId, self::VOTE_LIKE);
        return $this->getResultCommand($messageId);
    }

    public function actionDislikeMessage($messageId)
    {
        $this->AddOrChangeVote($messageId, self::VOTE_DISLIKE);
        return $this->getResultCommand($messageId);
    }

    public function send($text)
    {
        ResponseBuilder::fromUpdate($this->getUpdate())
            ->sendMessage(
                new \app\modules\bot\components\helpers\MessageText('text: '.$text)
            )
            ->build()[0]->send($this->getBotApi());
    }

    private function AddOrChangeVote(int $messageId, int $estimate)
    {
        $chat = $this->getTelegramChat();
        $chatId = $chat->id;
        $update = $this->getUpdate();

        $thisMessage = $this->getUpdate()->getMessage();
        if ($this->getUpdate()->getCallbackQuery()) {
            $thisMessage = $this->getUpdate()->getCallbackQuery()->getMessage();
            $anyMessageVote = RatingVote::find()->where(['message_id' => $messageId, 'chat_id' => $chatId])->one();
            $candidate_id = $anyMessageVote->provider_candidate_id;
        } else {
            $estimatedMessage = $thisMessage->getReplyToMessage();
            $candidate_id = $estimatedMessage->getFrom()->getId();
        }
        $voter_id = $thisMessage->getFrom()->getId();

        $vote = RatingVote::find()->where(['message_id' => $messageId, 'chat_id' => $chatId, 'provider_voter_id' => $voter_id])->one();
        if (!$vote) {
            $vote = new RatingVote();
            $vote->message_id = $messageId;
            $vote->provider_voter_id = $voter_id;
            $vote->provider_candidate_id = $candidate_id;
            $vote->chat_id = $chat->id;
        }
        $vote->vote = $estimate;
        $vote->save();
    }

    private function getResultCommand($messageId)
    {
        $chat = $this->getTelegramChat();
        $chatId = $chat->id;
        $likeVotes = RatingVote::find()->where(['message_id' => $messageId, 'chat_id' => $chatId, 'vote' => self::VOTE_LIKE])->count();
        $dislikeVotes = RatingVote::find()->where(['message_id' => $messageId, 'chat_id' => $chatId, 'vote' => self::VOTE_DISLIKE])->count();

        $commands = ResponseBuilder::fromUpdate($this->getUpdate())->sendMessage(
            $this->render('index'),
            [
                [
                    [
                        'callback_data' => self::createRoute('like-message', ['messageId' => $messageId]),
                        'text' => '👍 ' . Yii::t('bot', 'Like') . ' (' . $likeVotes . ')',
                    ],
                ],
                [
                    [
                        'callback_data' => self::createRoute('dislike-message', ['messageId' => $messageId]),
                        'text' => '👎 ' . Yii::t('bot', 'Disike') . ' (' . $dislikeVotes . ')',
                    ],
                ]
            ]
        )->build();
        return $commands;
    }
}
