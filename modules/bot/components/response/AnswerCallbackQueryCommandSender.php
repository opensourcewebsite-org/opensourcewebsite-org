<?php
	namespace app\modules\bot\components\response;

	use \TelegramBot\Api\BotApi;
	use \app\modules\bot\components\response\commands\AnswerCallbackQueryCommand;

	class AnswerCallbackQueryCommandSender implements ICommandSender
	{
		private $answerCallbackQueryCommand;

		public function __construct(AnswerCallbackQueryCommand $answerCallbackQueryCommand)
		{
			$this->answerCallbackQueryCommand = $answerCallbackQueryCommand;
		}

		public function sendCommand(BotApi $botApi)
		{
			return $botApi->answerCallbackQuery(
				$this->answerCallbackQueryCommand->callbackQueryId,
				$this->answerCallbackQueryCommand->text,
				$this->answerCallbackQueryCommand->showAlert
			);
		}
	}