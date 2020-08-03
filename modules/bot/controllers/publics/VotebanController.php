<?php

namespace app\modules\bot\controllers\publics;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\models\User;
use app\modules\bot\models\RatingVote;
use app\modules\bot\models\VotebanVote;
use app\modules\bot\models\VotebanVoting;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use app\modules\bot\components\helpers\Emoji;

/**
* Class VotebanController
*
* @package app\modules\bot\controllers\publics
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

        if (!$isBotAdmin) {
            return false;
        }

        return true;
    }

    public function actionIndex()
    {
        $result = null;
        $votingInitMessage = $this->getMessage();
        $spamMessage = $votingInitMessage->getReplyToMessage();
        $chat = $this->getTelegramChat();

        $votebanStatus = $chat->getSetting(ChatSetting::VOTE_BAN_STATUS);

        $ignoreMessage = (!$spamMessage) || !isset($votebanStatus) || ($votebanStatus->value != ChatSetting::VOTE_BAN_STATUS_ON);
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

        $votingInitMessage = $this->getMessage();
        try {
            $this->getBotApi()->deleteMessage($chat->chat_id, $votingInitMessage->getMessageId());
        } catch (HttpException $e) {
            echo 'ERROR: Public ' . $this->id . '/' . $this->action->id . ' MessageId ' . $votingInitMessage->getMessageId() . ' (deleteMessage): ' . $e->getMessage() . "\n";
        }

        if ($voter->getId() == $candidate->getId()) {
            $initVotingError = $this->sendMyselfVoteError();
        }

        $candidateIsAdmin = !isset($initVotingError) && $this->isCandidateChatAdmin($candidate->getId(), $chat->chat_id);
        if ($candidateIsAdmin) {
            $initVotingError = $this->sendCandidateIsAdminError();
        }

        $candidateIsBot = $this->getMessage()->getReplyToMessage()->getFrom()->isBot();
        if ($candidateIsBot) {
            $initVotingError = true;
        }

        return isset($initVotingError) ? true : false;
    }

    /**
    * @return array
    */
    private function voteUser($candidateId, $vote)
    {
        $chat = $this->getTelegramChat();
        $telagramUser = $this->getTelegramUser();

        $votingResult = null;
        $voterId = $telagramUser->provider_user_id;

        if ($voterId == $candidateId) {
            $votingResult = $this->sendMyselfVoteError();
        }

        if (!isset($votingResult)) {
            $voting = $this->getExistingOrCreateVoting();
            $currentUserVote = $this->getExistingOrCreateVote($voterId, $chat->id, $candidateId);
            $currentUserVote->vote = $vote;
            $currentUserVote->save();

            $limitSetting = $chat->getSetting(ChatSetting::VOTE_BAN_LIMIT);
            $votesLimit = isset($limitSetting) ? $limitSetting->value : ChatSetting::VOTE_BAN_LIMIT_DEFAULT;

            $kickVotes = VotebanVote::find()
                ->where([
                    'provider_candidate_id' => $candidateId,
                    'chat_id' => $chat->id,
                    'vote' => self::VOTING_POWER,
                ])
                ->count();

            $saveVotes = VotebanVote::find()
                ->where([
                    'provider_candidate_id' => $candidateId,
                    'chat_id' => $chat->id,
                    'vote' => -self::VOTING_POWER,
                ])
                ->count();

            $isVotingFinished = ($kickVotes >= $votesLimit) || ($saveVotes >= $votesLimit);

            if (!$isVotingFinished) {
                $command = $this->createVotingFormCommand($voting->provider_starter_id, $candidateId, $kickVotes, $saveVotes);
                $command->replyToMessageId = $voting->id ? null : $voting->candidate_message_id;
                $message = $command->send($this->getBotApi());
            }
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
    private function createVotingFormCommand($voterId, $candidateId, $kickVotes, $saveVotes)
    {
        $chat = $this->getTelegramChat();
        $limitSetting = $chat->getSetting(ChatSetting::VOTE_BAN_LIMIT);
        $votesLimit = isset($limitSetting) ? $limitSetting->value : ChatSetting::VOTE_BAN_LIMIT_DEFAULT;

        $voting = VotebanVoting::find()
            ->where([
                'chat_id' => $chat->id,
                'provider_candidate_id' => $candidateId,
            ])
            ->one();

        // TODO refactoring
        if (isset($voting)) {
            $command = $voting->command;
        } else {
            $command = $this->getMessage()->getText();
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
                ->select(['provider_candidate_id', 'rating' => 'sum(vote)'])
                ->asArray()
                ->all(),
            'provider_candidate_id',
            'rating'
        );

        $commandBuilder = $this->getResponseBuilder()
        ->editMessageTextOrSendMessage(
            $this->render('show-voting', [
                'providerVoterId' => $voterId,
                'providerCandidateId' => $candidateId,
                'userRating' => $ratings[$voterId] ?? 0,
                'candidateRating' => $ratings[$candidateId] ?? 0,
                'command' => $command,
            ]),
            [
                [
                    [
                        'callback_data' => self::createRoute('user-kick', ['userId' => $candidateId]),
                        'text' => Emoji::KICK_VOTE . ' ' . Yii::t('bot', 'Kick') . ' (' . $kickVotes . '/' . $votesLimit . ')',
                    ],
                ],
                [
                    [
                        'callback_data' => self::createRoute('user-save', ['userId' => $candidateId]),
                        'text' => Emoji::SAVE_VOTE . ' ' . Yii::t('bot', 'Save') . ' (' . $saveVotes . '/' . $votesLimit . ')',
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
            ->where([
                'provider_voter_id' => $voterId,
                'provider_candidate_id' => $candidateId,
                'chat_id' => $chatId,
            ])
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
        $chat = $this->getTelegramChat();

        $voting = null;
        $votingInitMessage = $this->getMessage();

        if (isset($votingInitMessage)) {
            $voter = $votingInitMessage->getFrom();
            $spamMessage = $votingInitMessage->getReplyToMessage();
            $candidate = $spamMessage->getFrom();
            $voting = new VotebanVoting();
            $voting->load([
                $voting->formName() => [
                    'provider_starter_id' => $voter->getId(),
                    'candidate_message_id' => $spamMessage->getMessageId(),
                    'provider_candidate_id' => $candidate->getId(),
                    'chat_id' => $chat->id,
                    'command' => $this->getMessage()->getText(),
                ]
            ]);

            $sameVotingForms = VotebanVoting::find()
                ->where([
                    'provider_candidate_id' => $candidate->getId(),
                    'chat_id' => $chat->id,
                ])
                ->all();

            if ($sameVotingForms) {
                foreach ($sameVotingForms as $sameVotingForm) {
                    try {
                        $this->getBotApi()->deleteMessage($chat->chat_id, $sameVotingForm->voting_message_id);
                    } catch (HttpException $e) {
                        echo 'ERROR: Public ' . $this->id . '/' . $this->action->id . ' MessageId ' . $sameVotingForm->voting_message_id . ' (deleteMessage): ' . $e->getMessage() . "\n";
                    }

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
            $votingMessageID = $this->getMessage()->getMessageId();
            $voting = VotebanVoting::find()
                ->where([
                    'voting_message_id' => $votingMessageID,
                ])
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
    private function kickUser($candidateId)
    {
        $chat = $this->getTelegramChat();

        $spamMessages = VotebanVoting::find()
            ->select('candidate_message_id')
            ->where([
                'provider_candidate_id' => $candidateId,
                'chat_id' => $chat->id
            ])
            ->groupBy('candidate_message_id')
            ->asArray()
            ->column();

        foreach ($spamMessages as $messageId) {
            try {
                $this->getBotApi()->deleteMessage($chat->chat_id, $messageId);
            } catch (HttpException $e) {
                echo 'ERROR: Public ' . $this->id . '/' . $this->action->id . ' MessageId ' . $messageId . ' (deleteMessage): ' . $e->getMessage() . "\n";
            }
        }

        $votersIds = VotebanVote::find()
            ->select('provider_voter_id')
            ->where([
                'provider_candidate_id' => $candidateId,
                'chat_id' => $chat->id,
                'vote' => self::VOTING_POWER,
            ])
            ->asArray()
            ->column();

        $this->clearUserVoteHistory($candidateId);

        try {
            $this->getBotApi()->kickChatMember($chat->chat_id, $candidateId);
        } catch (HttpException $e) {
            echo 'ERROR: Public ' . $this->id . '/' . $this->action->id . ' ProviderUserId ' . $candidateId . ' (kickChatMember): ' . $e->getMessage() . "\n";
        }

        return $this->getResponseBuilder()
            ->sendMessage(
                $this->render('user-kicked', [
                    'providerCandidateId' => $candidateId,
                    'votersIds' => $votersIds,
                ])
            )
            ->build();
    }

    /**
    * @return array
    */
    private function saveUser($candidateId)
    {
        $chat = $this->getTelegramChat();

        $votersIds = VotebanVote::find()
            ->select('provider_voter_id')
            ->where([
                'provider_candidate_id' => $candidateId,
                'chat_id' => $chat->id,
                'vote' => -self::VOTING_POWER,
            ])
            ->asArray()
            ->column();

        $this->clearUserVoteHistory($candidateId);

        return $this->getResponseBuilder()
            ->sendMessage(
                $this->render('user-saved', [
                    'providerCandidateId' => $candidateId,
                    'votersIds' => $votersIds,
                ])
            )
            ->build();
    }

    private function clearUserVoteHistory($candidateId)
    {
        $chat = $this->getTelegramChat();

        $votingMessagesIDs = VotebanVoting::find()
            ->select('voting_message_id')
            ->where([
                'provider_candidate_id' => $candidateId,
                'chat_id' => $chat->id,
            ])
            ->asArray()
            ->column();

        foreach ($votingMessagesIDs as $votingMessageID) {
            try {
                $this->getBotApi()->deleteMessage($chat->chat_id, $votingMessageID);
            } catch (HttpException $e) {
                echo 'ERROR: Public ' . $this->id . '/' . $this->action->id . ' MessageId ' . $votingMessageID . ' (deleteMessage): ' . $e->getMessage() . "\n";
            }
        }

        VotebanVote::deleteAll([
            'chat_id' => $chat->id,
            'provider_candidate_id' => $candidateId,
        ]);

        VotebanVoting::deleteAll([
            'chat_id' => $chat->id,
            'provider_candidate_id' => $candidateId,
        ]);
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
