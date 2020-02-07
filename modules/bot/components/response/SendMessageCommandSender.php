<?php
	namespace app\modules\bot\components\response;

	use \TelegramBot\Api\BotApi;
	use \app\modules\bot\components\response\commands\SendMessageCommand;

	class SendMessageCommandSender implements ICommandSender
	{
		private $sendMessageCommand;

		public function __construct(SendMessageCommand $sendMessageCommand)
		{
			$this->sendMessageCommand = $sendMessageCommand;
		}

		public function sendCommand(BotApi $botApi)
		{
			$res = $botApi->sendMessage(
				$this->sendMessageCommand->chatId,
		        $this->sendMessageCommand->text,
		        $this->sendMessageCommand->parseMode,
		        $this->sendMessageCommand->disablePreview,
		        $this->sendMessageCommand->replyToMessageId,
		        $this->sendMessageCommand->replyMarkup,
		        $this->sendMessageCommand->disableNotification
			);
			var_dump($this->sendMessageCommand->chatId,);
		}
	}