<?php
	namespace app\modules\bot\components\response;

	use \TelegramBot\Api\BotApi;
	use \app\modules\bot\components\response\commands\SendLocationCommand;

	class SendLocationCommandSender implements ICommandSender
	{
		private $sendLocationCommand;

		public function __construct(SendLocationCommand $sendLocationCommand)
		{
			$this->sendLocationCommand = $sendLocationCommand;
		}

		public function sendCommand(BotApi $botApi)
		{
			return $botApi->sendLocation(
				$this->sendLocationCommand->chatId,
		        $this->sendLocationCommand->latitude,
		        $this->sendLocationCommand->longitude,
		        $this->sendLocationCommand->replyToMessageId,
		        $this->sendLocationCommand->replyMarkup,
		        $this->sendLocationCommand->disableNotification,
		        $this->sendLocationCommand->livePeriod
			);
		}
	}