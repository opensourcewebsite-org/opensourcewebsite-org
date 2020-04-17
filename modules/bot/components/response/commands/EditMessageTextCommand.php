<?php
namespace app\modules\bot\components\response\commands;

use app\modules\bot\components\helpers\MessageText;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\HttpException;

class EditMessageTextCommand extends MessageTextCommand
{
    public function __construct(string $chatId, string $messageId, MessageText $messageText, array $optionalParams = [])
    {
        parent::__construct($messageText, $optionalParams);

        $this->chatId = $chatId;
        $this->messageId = $messageId;
    }
	
	/**
     * On success, the sent \TelegramBot\Api\Types\Message is returned.
     *
	*/
    public function send(BotApi $botApi)
    {
		$answer=false;
		try{
			$answer = $botApi->editMessageText(
				$this->chatId,
				$this->messageId,
				$this->text,
				$this->getOptionalProperty('parseMode', null),
				$this->getOptionalProperty('disablePreview', false),
				$this->getOptionalProperty('replyMarkup', null),
				$this->getOptionalProperty('inlineMessageId', null)
			);
		}catch(HttpException $e){
			if (YII_ENV_DEV) {
				throw $e;
			}
		}
		
		return $answer;

    }
}
