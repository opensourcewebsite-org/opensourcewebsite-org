<?php
	namespace app\modules\bot\components\request;

	class LocationRequestHandler implements IRequestHandler
	{
		public function getFrom($update)
		{
			if ($message = $update->getMessage())
			{
				$from = $message->getFrom();
			}

			return $from;
		}		

		public function getCommandText($update)
		{
			if (($message = $update->getMessage()) && $message->getLocation())
			{
				$commandText = '/update_location';
			}

			return $commandText;
		}
	}