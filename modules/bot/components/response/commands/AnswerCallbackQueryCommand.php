<?php
	namespace app\modules\bot\components\response\commands;

	class AnswerCallbackQueryCommand extends Command
	{
		public function __construct($array)
		{
			$this->callbackQueryId = NULL; //
			$this->text = NULL;
			$this->showAlert = FALSE;

			parent::__construct($array);
		}
	}