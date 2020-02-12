<?php
	namespace app\modules\bot\models;

	class BotClientState
	{
		private $fields = [];

		public function getKeyboardButtons()
		{
			return isset($this->fields['keyboardButtons']) ? $this->fields['keyboardButtons'] : [];
		}

		public function setKeyboardButtons($value)
		{
			$this->fields['keyboardButtons'] = $value;
		}

		public function getName()
		{
			return $this->fields['name'];
		}

		public function setName($value)
		{
			$this->fields['name'] = $value;
		}

		public function toJson()
		{
			return json_encode($this->fields);
		}

		public static function fromJson($json)
		{
			$state = new BotClientState();
			$state->fields = json_decode($json, true);
			return $state;
		}
	}