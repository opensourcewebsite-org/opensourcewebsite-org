<?php
	namespace app\modules\bot\components\response;

	use \TelegramBot\Api\BotApi;
	use \app\modules\bot\components\response\commands\EditMessageTextCommand;

	class EditMessageTextCommandSender implements ICommandSender
	{
		private $editMessageTextCommand;

		public function __construct(EditMessageTextCommand $editMessageTextCommand)
		{
			$this->editMessageTextCommand = $editMessageTextCommand;
		}

		public function sendCommand(BotApi $botApi)
		{
			return $botApi->editMessageText(
				$this->editMessageTextCommand->chatId,
		        $this->editMessageTextCommand->messageId,
		        $this->editMessageTextCommand->text,
		        $this->editMessageTextCommand->parseMode,
		        $this->editMessageTextCommand->disablePreview,
		        $this->editMessageTextCommand->replyMarkup,
		        $this->editMessageTextCommand->inlineMessageId
			);
		}
	}