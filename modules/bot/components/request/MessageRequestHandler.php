<?php
	namespace app\modules\bot\components\request;

	class MessageRequestHandler implements IRequestHandler
	{
		private $nextRequestHandler;

		public function __construct(IRequestHandler $nextRequestHandler = NULL)
		{
			$this->nextRequestHandler = $nextRequestHandler;
		}

		public function getFrom($update)
		{
			if ($message = $update->getMessage())
			{
				$from = $message->getFrom();
			}

			return (isset($from)
					? $from
					: (isset($this->nextRequestHandler)
						? $this->nextRequestHandler->getFrom($update)
						: NULL));
		}

		

		public function getText($update)
		{
			if ($message = $update->getMessage())
			{
				$text = $message->getText();
			}

			return (isset($text)
					? $text
					: (isset($this->nextRequestHandler)
						? $this->nextRequestHandler->getText($update)
						: NULL));
		}
	}