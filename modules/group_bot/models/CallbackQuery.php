<?php

namespace app\modules\group_bot\models;

class CallbackQuery {
	private $output;

	function __construct($output) {
		$this->output = $output;
	}

	function get_chat_id() {
		return $this->output['callback_query']['message']['chat']['id'];
	}

	function get_mid() {
		return $this->output['callback_query']['message']['message_id'];
	}

	function get_data() {
		return $this->output['callback_query']['data'];
	}
}

?>