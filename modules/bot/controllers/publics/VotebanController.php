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
use app\modules\bot\models\VotebanVotes;
use app\modules\bot\models\VotebanVotings;
use TelegramBot\Api\HttpException;
/**
 * Class HelloController
 *
 * @package app\controllers\bot
 */
class VotebanController extends Controller
{
	
	const VOTING_POWER=1;
	
    /**
     * @return array 
     */
	 
    public function actionIndex()
    {	
		$chat = $this->getTelegramChat();
		$isVotebanOn = $chat->getSetting(ChatSetting::VOTE_BAN_STATUS)->value;
		if($isVotebanOn!= ChatSetting::VOTE_BAN_STATUS_ON){
			return false;
		}
		
		$votingInitMessage=$this->getUpdate()->getMessage();
		
		if( isset($votingInitMessage) ){
			
			$deleteMessageCommand=new DeleteMessageCommand($chat->chat_id, $votingInitMessage->getMessageId());
			$deleteMessageCommand->send($this->botApi);
		}	
		
		$spamMessage =$votingInitMessage->getReplyToMessage();
		if(!$spamMessage){
			return false; 
		}
		
		$user =$votingInitMessage->getFrom();
		$candidate = $spamMessage->getFrom();
		

		if($user->getId()==$candidate->getId()){
			return false;
		}
		 
		if($this->_isCandidateChatAdmin($candidate->getId(),$chat->chat_id)){
			return $this->_sendAdminError();
		}
		
		return $this->actionUserKick($candidate->getId());
		
    }

    /**
     * @return array 
     */
	
	public function actionUserKick($user_id){
		
		return $this->_voteUser($user_id, self::VOTING_POWER);
	}

    /**
     * @return array 
     */
	
	public function actionUserSave($user_id){
		return $this->_voteUser($user_id, -self::VOTING_POWER);
	}
	
	
	
	
    /**
     * @return array 
     */
	
	private function _voteUser($candidate_id, $vote){
		
		$chat_id=$this->getTelegramChat()->id;				
		$username=$this->_getProviderUsernameById($candidate_id);
		
		
		
		$user = $this->getTelegramUser();
		if($user->provider_user_id == $candidate_id){
			return $this->_sendMyselfVoteError();
		}
		
		$currentUserVote=VotebanVotes::find()
						->where(['provider_voter_id'=>$user->provider_user_id,'chat_id'=>$chat_id,'provider_candidate_id'=>$candidate_id])
						->one();
						
		
		
		if( $this->getUpdate()->getCallbackQuery() !== null){
			
			$votingFormID=$this->getUpdate()->getCallbackQuery()->getMessage()->getMessageId();
			$voting=VotebanVotings::find()
						->where(['votingform_message_id'=>$votingFormID])
						->one();	

			if($voting){
				$starter=$this->_getProviderUsernameById($voting->provider_starter_id);
			}else{
				$starter=$user->provider_user_name;
			}
			
		}else{
			$starter=$user->provider_user_name	;
		}
		
			
		if($currentUserVote){
			if($currentUserVote->vote == $vote){
				//return $this->AlreadyVotedError();
			}else{
				$currentUserVote->vote = $vote;
				$currentUserVote->save();
			}
		}else{
			
			if( ($this->getUpdate()->getMessage()!== null) or ( ($this->getUpdate()->getCallbackQuery() !== null) && isset($voting) && $voting) ){
				$currentUserVote=new VotebanVotes();
				$currentUserVote->load([
					$currentUserVote->formName()=>[
						'provider_voter_id'=>$user->provider_user_id,						
						'provider_candidate_id'=>$candidate_id,
						'chat_id'=>$chat_id,
						'vote'=>$vote,
					]
				]);
				
				$currentUserVote->save();
			}else{
				return false;
			}
			
			
		}
	
		$chat = $this->getTelegramChat();
		$limitSetting = $chat->getSetting(ChatSetting::VOTE_BAN_LIMIT);
		$votesLimit = isset( $limitSetting ) ? $limitSetting->value : ChatSetting::VOTE_BAN_LIMIT_DEFAULT;
		
		$kickVotes=VotebanVotes::find()->where(['provider_candidate_id'=>$candidate_id,'chat_id'=>$chat_id,'vote'=>self::VOTING_POWER])->count();
		$saveVotes=VotebanVotes::find()->where(['provider_candidate_id'=>$candidate_id,'chat_id'=>$chat_id,'vote'=>-self::VOTING_POWER])->count();	
		
		if($kickVotes == $votesLimit){
			return $this->_kickUser($candidate_id);
		}elseif($saveVotes == $votesLimit){
			return $this->_saveUser($candidate_id);
		}else{
		
			
		
			$command =  array_pop(ResponseBuilder::fromUpdate($this->getUpdate())
				->editMessageTextOrSendMessage(
					$this->render('index',[
						'user'=>'@'.$starter,
						'candidate'=>'@'.$username
					]),
					[
						[ 
							[
								'callback_data' => self::createRoute('user-kick',['user_id'=>$candidate_id]),
								'text' => Yii::t('bot','🔫 Kick').' ('.$kickVotes.'/'.$votesLimit.')',
							],										
						],
						[
							[
								'callback_data' => self::createRoute('user-save',['user_id'=>$candidate_id]),
								'text' => Yii::t('bot','👼 Save').' ('.$saveVotes.'/'.$votesLimit.')',
							],
						]
					]
				)
				->build());
				
			$message= $command->send($this->botApi);
		
			$votingInitMessage=$this->getUpdate()->getMessage();
		
			if( isset($votingInitMessage) ){
				
				$spamMessage =$votingInitMessage->getReplyToMessage();
				$sender =$votingInitMessage->getFrom();
				$candidate = $spamMessage->getFrom();
					
					$voting = new VotebanVotings();
					$voting->load([
						$voting->formName()=>[
							'provider_candidate_id'=>$candidate_id,						
							'provider_starter_id' => $sender->getId(),
							'candidate_message_id'=>$spamMessage->getMessageId(),
							'chat_id'=>$chat_id,
							'votingform_message_id'=>$message->getMessageId(),
						]
					]);
					$voting->save();
				
				
				return false;
			}
		}
	}
	 


    /**
     * @return array 
     */

	private function _kickUser($user_id){
		
		$chat=$this->getTelegramChat();
		$spamMessages=VotebanVotings::find()->where(['provider_candidate_id'=>$user_id,'chat_id'=>$chat->id])->select('candidate_message_id')->groupBy('candidate_message_id')->asArray()->column();
		$chat_id=$chat->chat_id;		
		foreach($spamMessages as $message_id){		
			$deleteMessageCommand = new DeleteMessageCommand($chat_id, $message_id);
			$deleteMessageCommand->send($this->botApi);						
		}
		
		$votersIds=VotebanVotes::find()->where(['provider_candidate_id'=>$user_id,'chat_id'=>$chat->id,'vote'=>self::VOTING_POWER])->select('provider_voter_id')->asArray()->column();
		$votersNames=$this->_getProviderUsernamesByIds($votersIds);
		$this->_clearUserVoteHistory($user_id);
		$this->botApi->kickChatMember($chat_id, $user_id);
		
		return ResponseBuilder::fromUpdate($this->getUpdate())
            ->sendMessage(
                $this->render('user-kicked',[
					'user'=>'@'.$this->_getProviderUsernameById($user_id),
					'voters'=>implode(', ',$votersNames)
				])
            )
            ->build();
			

	}
	
	
    /**
     * @return array 
     */
	
	private function _saveUser($user_id){		
		$chat=$this->getTelegramChat();
		$votersIds=VotebanVotes::find()->where(['provider_candidate_id'=>$user_id,'chat_id'=>$chat->id,'vote'=>-self::VOTING_POWER])->select('provider_voter_id')->asArray()->column();
		$votersNames=$this->_getProviderUsernamesByIds($votersIds);
		$this->_clearUserVoteHistory($user_id);
		return ResponseBuilder::fromUpdate($this->getUpdate())
            ->sendMessage(
                $this->render('user-saved',[
					'user'=>'@'.$this->_getProviderUsernameById($user_id),
					'voters'=>implode(', ',$votersNames)
				])
            )
            ->build();
			
		
	}


	private function _clearUserVoteHistory($user_id){
		$chat=$this->getTelegramChat();
		$votingFormsIDs=VotebanVotings::find()->where(['provider_candidate_id'=>$user_id,'chat_id'=>$chat->id])->select('votingform_message_id')->asArray()->column();		
		
		foreach($votingFormsIDs as $votingFormID){
			$deleteMessageCommand = new DeleteMessageCommand($chat->chat_id, $votingFormID);
			$deleteMessageCommand->send($this->botApi);	
		}
		
		
		VotebanVotes::deleteAll([
			'chat_id' => $this->getTelegramChat()->id,
			'provider_candidate_id'=>$user_id
		]);
		
		VotebanVotings::deleteAll([
			'chat_id' => $this->getTelegramChat()->id,
			'provider_candidate_id'=>$user_id
		]);
	}
	
	private function _getProviderUsernamesByIds(array $ids){
		
		$names=[];
		foreach( $ids as $id ){
			$name=$this->_getProviderUsernameById($id);
			if($name){
				$names[]='@'.$name;
			}
		}
		return $names;
	}
	
	private function _getProviderUsernameById($user_id){
		try{
			return $this->botApi->getChatMember(
						$this->getTelegramChat()->chat_id,
						$user_id
				   )->getUser()->getUsername();
		}catch(HttpException $e){
			return false;
		}
	}
	
	private function _isCandidateChatAdmin($userId,$chatId){
		
		$administrators = $this->botApi->getChatAdministrators($chatId);		
		return in_array(
						$userId,
						ArrayHelper::getColumn($administrators,function($el){
							return $el->getUser()->getId();
						})
		);
		
	}
	private function _sendAdminError(){
		return false;
	}
	private function _sendAlreadyVotedError(){
		return false;
	}
	private function _sendMyselfVoteError(){
		return false;
	}
}
