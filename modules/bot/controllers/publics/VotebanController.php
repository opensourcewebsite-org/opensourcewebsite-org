<?php

namespace app\modules\bot\controllers\publics;

use app\modules\bot\components\Controller as Controller;
use app\modules\bot\components\response\commands\DeleteMessageCommand;
use app\modules\bot\components\response\commands\EditMessageTextCommand;
use app\modules\bot\components\response\commands\SendMessageCommand;
use app\modules\bot\components\response\ResponseBuilder;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\models\VotebanVote;
use app\modules\bot\models\VotebanVoting;
use TelegramBot\Api\HttpException;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class VotebanController
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
        $result = null;
        $votingInitMessage = $this->getUpdate()->getMessage();
        $spamMessage = $votingInitMessage->getReplyToMessage();
        $chat = $this->getTelegramChat();
        $isVotebanOn = $chat->getSetting(ChatSetting::VOTE_BAN_STATUS)->value;

        $ignoreMessage = (!$spamMessage) || ($isVotebanOn != ChatSetting::VOTE_BAN_STATUS_ON);
        if ($ignoreMessage) {
            $result = $this->sendIgnoreMessageError();
        }

        $voter = $votingInitMessage->getFrom();
        $candidate = $spamMessage->getFrom();
        $hasVotebanRights = !isset($result) && !$this->isDenyInitVoteban($voter, $candidate);
        if ($hasVotebanRights) {
            $result = $this->actionUserKick($candidate->getId());
        }

        $result = isset($result) ? $result : $this->sendUndefinedError();
        return $result;
    }

    /**
    *
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
    *
    * @return boolean
    */
    private function isDenyInitVoteban($voter, $candidate)
    {
        $initVotingError = null;
        $chat = $this->getTelegramChat();

        $votingInitMessage = $this->getUpdate()->getMessage();
        $deleteMessageCommand = new DeleteMessageCommand($chat->chat_id, $votingInitMessage->getMessageId());
        $deleteMessageCommand->send($this->botApi);

        if ($voter->getId() == $candidate->getId()) {
            $initVotingError = $this->sendMyselfVoteError();
        }

        $candidateIsAdmin = !isset($initVotingError) && $this->isCandidateChatAdmin($candidate->getId(), $chat->chat_id);
        if ($candidateIsAdmin) {
            $initVotingError = $this->sendCandidateIsAdminError();
        }

        return isset($initVotingError) ? true : false;
    }


    /**
     * @return array
     */

    private function voteUser($candidateId, $vote)
    {
        $votingResult = null;
        $chatId = $this->getTelegramChat()->id;

        $user = $this->getTelegramUser();
        $voterId = $user->provider_user_id;
        if ($voterId == $candidateId) {
            $votingResult = $this->sendMyselfVoteError();
        }

        if (!isset($votingResult)) {
            $voting = $this->getExistingOrCreateVoting();
        }

        if (!isset($votingResult) && !isset($voting)) {
            $votingResult = $this->sendUndefinedError();
        }

        if (!isset($votingResult)) {
            $currentUserVote = $this->getExistingOrCreateVote($voterId, $chatId, $candidateId);
            if ($currentUserVote->vote == $vote) {
                $votingResult = $this->alreadyVotedError();
            }
        }

        if (!isset($votingResult)) {
            $currentUserVote->vote = $vote;
            $currentUserVote->save();

            $currentUserVote->vote = $vote;
            $currentUserVote->save();

            $chat = $this->getTelegramChat();
            $limitSetting = $chat->getSetting(ChatSetting::VOTE_BAN_LIMIT);
            $votesLimit = isset($limitSetting) ? $limitSetting->value : ChatSetting::VOTE_BAN_LIMIT_DEFAULT;

            $kickVotes = VotebanVote::find()->where(['provider_candidate_id' => $candidateId,'chat_id' => $chatId,'vote' => self::VOTING_POWER])->count();
            $saveVotes = VotebanVote::find()->where(['provider_candidate_id' => $candidateId,'chat_id' => $chatId,'vote' => -self::VOTING_POWER])->count();

            if ($kickVotes >= $votesLimit) {
                $votingResult = $this->kickUser($candidateId);
            }

            if (!isset($votingResult) && ($saveVotes >= $votesLimit)) {
                $votingResult = $this->saveUser($candidateId);
            }

            if (!isset($votingResult)) {
                $starter = $this->getProviderUsernameById($voting->provider_starter_id);
                $command = $this->createVotingFormCommand($starter, $candidateId, $kickVotes, $saveVotes);
                $message = $command->send($this->botApi);
                if ($message) {
                    $voting->voting_message_id = $message->getMessageId();
                    $voting->save();
                }
                $votingResult = [];
            }
        }

        $votingResult = isset($votingResult) ? $votingResult : [];
        return $votingResult;
    }

    /**
    *
    * @return MessageTextCommand
    */
    private function createVotingFormCommand($starterName, $candidateId, $kickVotes, $saveVotes)
    {
        $chat = $this->getTelegramChat();
        $limitSetting = $chat->getSetting(ChatSetting::VOTE_BAN_LIMIT);
        $votesLimit = isset($limitSetting) ? $limitSetting->value : ChatSetting::VOTE_BAN_LIMIT_DEFAULT;
        $candidateName = $this->getProviderUsernameById($candidateId);

        $commands=ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'user' => '@' . $starterName,
                    'candidate' => '@' . $candidateName
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('user-kick', ['userId' => $candidateId]),
                            'text' => '🔫' . ' ' . Yii::t('bot', 'Kick') . ' (' . $kickVotes . '/' . $votesLimit . ')',
                        ],
                    ],
                    [
                        [
                            'callback_data' => self::createRoute('user-save', ['userId' => $candidateId]),
                            'text' => '👼' . Yii::t('bot', 'Save') . ' (' . $saveVotes . '/' . $votesLimit . ')',
                        ],
                    ]
                ]
            )
            ->build();

        $command =  array_pop($commands);
        return $command;
    }

    /**
    *
    * @return VotebanVote
    */
    private function getExistingOrCreateVote($voterId, $chatId, $candidateId)
    {
        $currentUserVote = VotebanVote::find()
                ->where(['provider_voter_id' => $voterId, 'chat_id' => $chatId,'provider_candidate_id' => $candidateId])
                ->one();
        if (!$currentUserVote) {
            $currentUserVote = new VotebanVote();
            $currentUserVote->load([
                    $currentUserVote->formName() => [
                        'provider_voter_id' => $voterId,
                        'provider_candidate_id' => $candidateId,
                        'chat_id' => $chatId,
                    ]
                ]);
        }

        return $currentUserVote;
    }

    /**
    *
    * @return VotebanVoting
    **/
    private function getExistingOrCreateVoting()
    {
        $voting = $this->getExistingVotingFromCallback();

        if (!$voting->id) {
            $voting = $this->createVotingFromMessage();
        }
        return $voting;
    }
    /**
    *
    * @return VotebanVoting
    */
    private function createVotingFromMessage()
    {
        $voting = null;
        $votingInitMessage = $this->getUpdate()->getMessage();
        if (isset($votingInitMessage)) {
            $sender = $votingInitMessage->getFrom();
            $spamMessage = $votingInitMessage->getReplyToMessage();
            $spamer = $spamMessage->getFrom();
            $chatId = $this->getTelegramChat()->id;
            $voting = new VotebanVoting();
            $voting->load([
                    $voting->formName() => [
                        'provider_starter_id' => $sender->getId(),
                        'candidate_message_id' => $spamMessage->getMessageId(),
                        'provider_candidate_id' => $spamer->getId(),
                        'chat_id' => $chatId
                    ]
                ]);
        }
        return $voting;
    }

    /**
    * @return VotebanVoting
    */
    private function getExistingVotingFromCallback()
    {
        if ($this->isCallbackQuery()) {
            $votingMessageID = $this->getUpdate()->getCallbackQuery()->getMessage()->getMessageId();
            $voting = VotebanVoting::find()
                        ->where(['voting_message_id' => $votingMessageID])
                        ->one();
        }
        if (!isset($voting)) {
            $voting = new VotebanVoting();
        }
        return $voting;
    }

    /**
    *
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
        $votingMessagesIDs = VotebanVoting::find()->where(['provider_candidate_id' => $userId,'chat_id' => $chat->id])->select('voting_message_id')->asArray()->column();

        foreach ($votingMessagesIDs as $votingMessageID) {
            $deleteMessageCommand = new DeleteMessageCommand($chat->chat_id, $votingMessageID);
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
                $names[] = '@' . $name;
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
            return '';
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

    private function isCallbackQuery()
    {
        return $this->getUpdate()->getCallbackQuery() !== null;
    }

    private function sendCandidateIsAdminError()
    {
        Yii::warning('Voteban admin attempt');
        return [];
    }

    private function sendMyselfVoteError()
    {
        Yii::warning('Voteban himself attempt');
        return [];
    }

    private function sendIgnoreMessageError()
    {
        Yii::warning('Ignore message');
        return [];
    }

    private function sendUserUndefinedError()
    {
        Yii::error('Undefined user voteban error');
        return [];
    }

    private function alreadyVotedError()
    {
        Yii::warning('User already voted');
        return null;
    }

    private function sendUndefinedError()
    {
        Yii::warning('Undefined voteban error');
        return [];
    }
}
