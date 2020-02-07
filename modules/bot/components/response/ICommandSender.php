<?php
	namespace app\modules\bot\components\response;

	use \TelegramBot\Api\BotApi;

	interface ICommandSender
	{
		public function sendCommand(BotApi $botApi);
	}