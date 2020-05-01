<?php

namespace app\modules\bot\controllers\publics;

use app\modules\bot\components\Controller as Controller;
use app\modules\bot\components\response\commands\DeleteMessageCommand;
use app\modules\bot\components\response\commands\EditMessageTextCommand;
use app\modules\bot\components\response\commands\SendMessageCommand;
use app\modules\bot\components\response\ResponseBuilder;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\models\User;
use app\modules\bot\models\VotebanVote;
use app\modules\bot\models\VotebanVoting;
use TelegramBot\Api\HttpException;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
* Class VotebanController
*
* @package app\controllers\bot
*/
class VotebanController extends Controller
{
    const VOTING_POWER = 1;

    /**
    * @return boolean
    */
    public function beforeAction($action)
    {
        $chat = $this->getTelegramChat();
        $chatId = $chat->chat_id;

        $isBotAdmin = false;
        $botUser = User::find()->where(['provider_user_name' => $this->getBotName()])->one();

        if ($botUser) {
            $isBotAdmin = ChatMember::find()->where(['chat_id' => $chat->id, 'user_id' => $botUser->id, 'status' => ChatMember::STATUS_ADMINISTRATOR])->exists();
        }
        if (!$isBotAdmin) {
            return false;
        }
        return true;
    }

    public function actionIndex()
    {
        $result = null;
        $votingInitMessage = $this->getUpdate()->getMessage();
        $spamMessage = $votingInitMessage->getReplyToMessage();
        $chat = $this->getTelegramChat();
        $isVotebanOn = $chat->getSetting(ChatSetting::VOTE_BAN_STATUS)->value;

        $ignoreMessage = (!$spamMessage) || ($isVotebanOn != ChatSetting::VOTE_BAN_STATUS_ON);
        if ($ignoreMessage) {
            return $this->sendIgnoreMessageError();
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
    public function actionUserSave($userId = 0)
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
        $deleteMessageCommand->send($this->getBotApi());

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
            $currentUserVote = $this->getExistingOrCreateVote($voterId, $chatId, $candidateId);
            $currentUserVote->vote = $vote;
            $currentUserVote->save();

            $chat = $this->getTelegramChat();
            $limitSetting = $chat->getSetting(ChatSetting::VOTE_BAN_LIMIT);
            $votesLimit = isset($limitSetting) ? $limitSetting->value : ChatSetting::VOTE_BAN_LIMIT_DEFAULT;

            $kickVotes = VotebanVote::find()->where(['provider_candidate_id' => $candidateId,'chat_id' => $chatId,'vote' => self::VOTING_POWER])->count();
            $saveVotes = VotebanVote::find()->where(['provider_candidate_id' => $candidateId,'chat_id' => $chatId,'vote' => -self::VOTING_POWER])->count();

            $starter = $this->getProviderUsernameById($voting->provider_starter_id);
            $command = $this->createVotingFormCommand($starter, $candidateId, $kickVotes, $saveVotes);
            $command->replyToMessageId = $voting->id ? null : $voting->candidate_message_id;
            $message = $command->send($this->getBotApi());

            if (!$voting->id) {
                if ($message) {
                    $voting->voting_message_id = $message->getMessageId();
                    $voting->save();
                }
                $votingResult = [];
            }

            if ($kickVotes >= $votesLimit) {
                $votingResult = $this->kickUser($candidateId);
            }
            if ($saveVotes >= $votesLimit) {
                $votingResult = $this->saveUser($candidateId);
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

        $commandBuilder = ResponseBuilder::fromUpdate($this->getUpdate())
        ->editMessageTextOrSendMessage(
            $this->render('index', [
                'user' => $starterName,
                'candidate' => $candidateName
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
        );

        $commands = $commandBuilder->build();
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
        $voting = $this->getExistingVotingFormCallback();

        if (!$voting->id) {
            $voting = $this->createVotingFormMessage();
        }
        return $voting;
    }
    /**
    *
    * @return VotebanVoting
    */
    private function createVotingFormMessage()
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

            $sameVotingForms = VotebanVoting::find()->where(['provider_candidate_id' => $spamer->getId(),'chat_id' => $chatId])->all();

            if ($sameVotingForms) {
                foreach ($sameVotingForms as $sameVotingForm) {
                    $deleteMessageCommand = new DeleteMessageCommand($this->getTelegramChat()->chat_id, $sameVotingForm->voting_message_id);
                    $deleteMessageCommand->send($this->getBotApi());
                    $sameVotingForm->delete();
                }
            }
        }
        return $voting;
    }

    /**
    * @return VotebanVoting
    */
    private function getExistingVotingFormCallback()
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
            $deleteMessageCommand->send($this->getBotApi());
        }

        $votersIds = VotebanVote::find()->where(['provider_candidate_id' => $userId,'chat_id' => $chat->id,'vote' => self::VOTING_POWER])->select('provider_voter_id')->asArray()->column();
        $votersNames = $this->getProviderUsernamesByIds($votersIds);
        $this->clearUserVoteHistory($userId);
        $this->getBotApi()->kickChatMember($chatId, $userId);

        return ResponseBuilder::fromUpdate($this->getUpdate())
        ->sendMessage(
            $this->render('user-kicked', [
                'user' => $this->getProviderUsernameById($userId),
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
                    'user' => $this->getProviderUsernameById($userId),
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
            $deleteMessageCommand->send($this->getBotApi());
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
                $names[] = $name;
            }
        }
        return $names;
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

    private function isCandidateChatAdmin($userId, $chatId)
    {
        $administrators = $this->getBotApi()->getChatAdministrators($chatId);
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

    private function sendUndefinedError()
    {
        Yii::warning('Undefined voteban error');
        return [];
    }
}
