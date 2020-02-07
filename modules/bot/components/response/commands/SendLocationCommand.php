<?php
	namespace app\modules\bot\components\response\commands;

	class SendLocationCommand extends Command
	{
		public function __construct($array)
		{
			$this->chatId = NULL; //
	        $this->latitude = NULL; //
	        $this->longitude = NULL; //
	        $this->replyToMessageId = NULL;
	        $this->replyMarkup = NULL;
	        $this->disableNotification = FALSE;
	        $this->livePeriod = NULL;

			parent::__construct($array);
		}
	}