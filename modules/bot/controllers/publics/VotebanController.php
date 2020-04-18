<?php

namespace app\modules\bot\controllers\publics;

use app\modules\bot\components\response\commands\SendMessageCommand;
use app\modules\bot\components\response\commands\EditMessageTextCommand;
use app\modules\bot\components\response\commands\DeleteMessageCommand;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use Yii;
use app\modules\bot\components\Controller as Controller;
use app\modules\bot\components\response\ResponseBuilder;
use yii\helpers\ArrayHelper;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\models\VotebanVote;
use app\modules\bot\models\VotebanVoting;
use TelegramBot\Api\HttpException;

/**
 * Class HelloController
 *
 * @package app\controllers\bot
 */
class VotebanController extends Controller
{
    const VOTING_POWER = 1;

    /**
     * @return array
     */

    public function actionIndex()
    {
        $chat = $this->getTelegramChat();
        $isVotebanOn = $chat->getSetting(ChatSetting::VOTE_BAN_STATUS)->value;
        if ($isVotebanOn != ChatSetting::VOTE_BAN_STATUS_ON) {
            return [];
        }

        $votingInitMessage = $this->getUpdate()->getMessage();

        $spamMessage = $votingInitMessage->getReplyToMessage();
        if (!$spamMessage) {
            return [];
        }

        if (isset($votingInitMessage)) {
            $deleteMessageCommand = new DeleteMessageCommand($chat->chat_id, $votingInitMessage->getMessageId());
            $deleteMessageCommand->send($this->botApi);
        }

        $user = $votingInitMessage->getFrom();
        $candidate = $spamMessage->getFrom();

        if ($user->getId() == $candidate->getId()) {
            return [];
        }

        if ($this->isCandidateChatAdmin($candidate->getId(), $chat->chat_id)) {
            return $this->sendAdminError();
        }

        return $this->actionUserKick($candidate->getId());
    }

    /**
     * @return array
     */

    public function actionUserKick($userId)
    {
        return $this->voteUser($userId, self::VOTING_POWER);
    }

    /**
     * @return array
     */

    public function actionUserSave($userId)
    {
        return $this->voteUser($userId, -self::VOTING_POWER);
    }

    /**
     * @return array
     */

    private function voteUser($candidateId, $vote)
    {
        $chatId = $this->getTelegramChat()->id;
        $username = $this->getProviderUsernameById($candidateId);

        $user = $this->getTelegramUser();
        if ($user->provider_user_id == $candidateId) {
            return $this->sendMyselfVoteError();
        }

        $currentUserVote = VotebanVote::find()
                        ->where(['provider_voter_id' => $user->provider_user_id,'chat_id' => $chatId,'provider_candidate_id' => $candidateId])
                        ->one();

        if ($this->getUpdate()->getCallbackQuery() !== null) {
            $votingFormID = $this->getUpdate()->getCallbackQuery()->getMessage()->getMessageId();
            $voting = VotebanVoting::find()
                        ->where(['voting_message_id' => $votingFormID])
                        ->one();

            if ($voting) {
                $starter = $this->getProviderUsernameById($voting->provider_starter_id);
            } else {
                $starter = $user->provider_user_name;
            }
        } else {
            $starter = $user->provider_user_name	;
        }

        if ($currentUserVote) {
            if ($currentUserVote->vote == $vote) {
                //return $this->AlreadyVotedError();
            } else {
                $currentUserVote->vote = $vote;
                $currentUserVote->save();
            }
        } else {
            if (($this->getUpdate()->getMessage() !== null) or (($this->getUpdate()->getCallbackQuery() !== null) && isset($voting) && $voting)) {
                $currentUserVote = new VotebanVote();
                $currentUserVote->load([
                    $currentUserVote->formName() => [
                        'provider_voter_id' => $user->provider_user_id,
                        'provider_candidate_id' => $candidateId,
                        'chat_id' => $chatId,
                        'vote' => $vote,
                    ]
                ]);

                $currentUserVote->save();
            } else {
                return [];
            }
        }

        $chat = $this->getTelegramChat();
        $limitSetting = $chat->getSetting(ChatSetting::VOTE_BAN_LIMIT);
        $votesLimit = isset($limitSetting) ? $limitSetting->value : ChatSetting::VOTE_BAN_LIMIT_DEFAULT;

        $kickVotes = VotebanVote::find()->where(['provider_candidate_id' => $candidateId,'chat_id' => $chatId,'vote' => self::VOTING_POWER])->count();
        $saveVotes = VotebanVote::find()->where(['provider_candidate_id' => $candidateId,'chat_id' => $chatId,'vote' => -self::VOTING_POWER])->count();

        if ($kickVotes >= $votesLimit) {
            return $this->kickUser($candidateId);
        } elseif ($saveVotes >= $votesLimit) {
            return $this->saveUser($candidateId);
        } else {
            $commands=ResponseBuilder::fromUpdate($this->getUpdate())
                ->editMessageTextOrSendMessage(
                    $this->render('index', [
                        'user' => '@' . $starter,
                        'candidate' => '@' . $username
                    ]),
                    [
                        [
                            [
                                'callback_data' => self::createRoute('user-kick', ['user_id' => $candidateId]),
                                'text' => '🔫' . ' ' . Yii::t('bot', 'Kick') . ' (' . $kickVotes . '/' . $votesLimit . ')',
                            ],
                        ],
                        [
                            [
                                'callback_data' => self::createRoute('user-save', ['user_id' => $candidateId]),
                                'text' => '👼' . Yii::t('bot', 'Save') . ' (' . $saveVotes . '/' . $votesLimit . ')',
                            ],
                        ]
                    ]
                )
                ->build();

            $command =  array_pop($commands);
            $message = $command->send($this->botApi);
            $votingInitMessage = $this->getUpdate()->getMessage();

            if (isset($votingInitMessage)) {
                $spamMessage = $votingInitMessage->getReplyToMessage();
                $sender = $votingInitMessage->getFrom();
                $voting = new VotebanVoting();
                $voting->load([
                        $voting->formName() => [
                            'provider_candidate_id' => $candidateId,
                            'provider_starter_id' => $sender->getId(),
                            'candidate_message_id' => $spamMessage->getMessageId(),
                            'chat_id' => $chatId,
                            'voting_message_id' => $message->getMessageId(),
                        ]
                    ]);
                $voting->save();
                return [];
            }
        }
    }

    /**
     * @return array
     */

    private function kickUser($userId)
    {
        $chat = $this->getTelegramChat();
        $spamMessages = VotebanVoting::find()->where(['provider_candidate_id' => $userId,'chat_id' => $chat->id])->select('candidate_message_id')->groupBy('candidate_message_id')->asArray()->column();
        $chatId = $chat->chat_id;
        foreach ($spamMessages as $messageId) {
            $deleteMessageCommand = new DeleteMessageCommand($chatId, $messageId);
            $deleteMessageCommand->send($this->botApi);
        }

        $votersIds = VotebanVote::find()->where(['provider_candidate_id' => $userId,'chat_id' => $chat->id,'vote' => self::VOTING_POWER])->select('provider_voter_id')->asArray()->column();
        $votersNames = $this->getProviderUsernamesByIds($votersIds);
        $this->clearUserVoteHistory($userId);
        $this->botApi->kickChatMember($chatId, $userId);

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->sendMessage(
                $this->render('user-kicked', [
                    'user' => '@' . $this->getProviderUsernameById($userId),
                    'voters' => implode(', ', $votersNames)
                ])
            )
            ->build();
    }

    /**
     * @return array
     */

    private function saveUser($userId)
    {
        $chat = $this->getTelegramChat();
        $votersIds = VotebanVote::find()->where(['provider_candidate_id' => $userId,'chat_id' => $chat->id,'vote' => -self::VOTING_POWER])->select('provider_voter_id')->asArray()->column();
        $votersNames = $this->getProviderUsernamesByIds($votersIds);
        $this->clearUserVoteHistory($userId);
        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->sendMessage(
                $this->render('user-saved', [
                    'user' => '@' . $this->getProviderUsernameById($userId),
                    'voters' => implode(', ', $votersNames)
                ])
            )
            ->build();
    }

    private function clearUserVoteHistory($userId)
    {
        $chat = $this->getTelegramChat();
        $votingFormsIDs = VotebanVoting::find()->where(['provider_candidate_id' => $userId,'chat_id' => $chat->id])->select('voting_message_id')->asArray()->column();

        foreach ($votingFormsIDs as $votingFormID) {
            $deleteMessageCommand = new DeleteMessageCommand($chat->chat_id, $votingFormID);
            $deleteMessageCommand->send($this->botApi);
        }

        VotebanVote::deleteAll([
            'chat_id' => $this->getTelegramChat()->id,
            'provider_candidate_id' => $userId
        ]);

        VotebanVoting::deleteAll([
            'chat_id' => $this->getTelegramChat()->id,
            'provider_candidate_id' => $userId
        ]);
    }

    private function getProviderUsernamesByIds(array $ids)
    {
        $names = [];
        foreach ($ids as $id) {
            $name = $this->getProviderUsernameById($id);
            if ($name) {
                $names[] = ('@' . $name);
            }
        }
        return $names;
    }

    private function getProviderUsernameById($userId)
    {
        try {
            return $this->botApi->getChatMember(
                $this->getTelegramChat()->chat_id,
                $userId
            )->getUser()->getUsername();
        } catch (HttpException $e) {
            return [];
        }
    }

    private function isCandidateChatAdmin($userId, $chatId)
    {
        $administrators = $this->botApi->getChatAdministrators($chatId);
        return in_array(
            $userId,
            ArrayHelper::getColumn($administrators, function ($el) {
                return $el->getUser()->getId();
            })
        );
    }

    private function sendAdminError()
    {
        return [];
    }

    private function sendMyselfVoteError()
    {
        return [];
    }
}
