<?php
	namespace app\modules\bot\components\request;

	class CallbackQueryRequestHandler implements IRequestHandler
	{
		public function getFrom($update)
		{
			if ($callbackQuery = $update->getCallbackQuery())
			{
				$from = $callbackQuery->getFrom();
			}

			return $from;
		}

		public function getCommandText($update)
		{
			if ($callbackQuery = $update->getCallbackQuery())
			{
				$commandText = $callbackQuery->getData();
			}

			return $commandText;
		}
	}