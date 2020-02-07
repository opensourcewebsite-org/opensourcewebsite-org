<?php
	namespace app\modules\bot\components\response\commands;

	class EditMessageTextCommand extends Command
	{
		public function __construct($array)
		{
			$chatId = NULL; //
	        $messageId = NULL; //
	        $text = NULL; //
	        $parseMode = NULL;
	        $disablePreview = FALSE;
	        $replyMarkup = NULL;
	        $inlineMessageId = NULL;
	        
			parent::__construct($array);
		}
	}