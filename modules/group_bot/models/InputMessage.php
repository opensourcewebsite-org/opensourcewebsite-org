<?php

namespace app\modules\group_bot\models;

class InputMessage {
	private $output;

	public function __construct($output) {
		$this->output = $output;
	}

	public function get_from_id() {
		return $this->output['message']['from']['id'];
	}

	public function get_chat_id() {
		return $this->output['message']['chat']['id'];
	}

	public function get_text() {
		return isset($this->output['message']['text']) ? $this->output['message']['text'] : null;
	}

	public function get_message_id() {
		return $this->output['message']['message_id'];
	}

	public function get_chat_type() {
		return $this->output['message']['chat']['type'];
	}

	public function get_chat_title() {
		return $this->output['message']['chat']['title'];
	}
}

?>