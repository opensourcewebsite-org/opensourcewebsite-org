<?php
	namespace app\modules\bot\components\request;

	class CallbackQueryRequestHandler implements IRequestHandler
	{
		private $nextRequestHandler;

		public function __construct(IRequestHandler $nextRequestHandler = NULL)
		{
			$this->nextRequestHandler = $nextRequestHandler;
		}

		public function getFrom($update)
		{
			if ($callbackQuery = $update->getCallbackQuery())
			{
				$from = $callbackQuery->getFrom();
			}

			return (isset($from)
					? $from
					: (isset($this->nextRequestHandler)
						? $this->nextRequestHandler->getFrom($update)
						: NULL));
		}

		public function getText($update)
		{
			if ($callbackQuery = $update->getCallbackQuery())
			{
				$text = $callbackQuery->getData();
			}

			return (isset($text)
					? $text
					: (isset($this->nextRequestHandler)
						? $this->nextRequestHandler->getText($update)
						: NULL));
		}
	}