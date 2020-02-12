<?php
namespace app\modules\bot\components\response;

use \TelegramBot\Api\BotApi;

class SendMessageCommand extends Command
{
	public function __construct($chatId, $text, $optionalParams = [])
	{
		parent::__construct($optionalParams);

		$this->chatId = $chatId;
		$this->text = $text;
	}

	public function send(BotApi $botApi)
	{
		$botApi->sendMessage(
			$this->chatId,
			$this->text,
			$this->getOptionalProperty('parseMode', NULL),
			$this->getOptionalProperty('disablePreview', FALSE),
			$this->getOptionalProperty('replyToMessageId', NULL),
			$this->getOptionalProperty('replyMarkup', NULL),
			$this->getOptionalProperty('disableNotification', FALSE),
		);
	}
}