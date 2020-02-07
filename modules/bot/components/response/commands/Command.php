<?php
	namespace app\modules\bot\components\response\commands;

	abstract class Command
	{
		private $fields = [];

		protected function __construct($array)
		{
			foreach ($array as $key => $value) {
				$this->{$key} = $value;
			}
		}

		public function __get($name)
		{
			return $this->fields[$name];
		}

		public function __set($name, $value)
		{
			$this->fields[$name] = $value;
		}
	}