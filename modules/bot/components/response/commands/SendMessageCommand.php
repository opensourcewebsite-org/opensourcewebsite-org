<?php
	namespace app\modules\bot\components\response\commands;

	class SendMessageCommand extends Command
	{
		public function __construct($array)
		{
			$this->chatId = NULL; //
	        $this->text = NULL; //
	        $this->parseMode = NULL;
	        $this->disablePreview = FALSE;
	        $this->replyToMessageId = NULL;
	        $this->replyMarkup = NULL;
	        $this->disableNotification = FALSE;

			parent::__construct($array);
		}
	}