<?php

namespace app\modules\group_bot\models;

/**
 * Class InputMessage
 *
 * @package app\modules\group_bot\models
 */
class InputMessage {
	/**
     * Telegram Request
     */
	private $_output;

	/**
     * @param array $output
     */
	public function __construct($output)
	{
		$this->_output = $output;
	}

	/**
     * @return int
     */
	public function getFromId()
	{
		return $this->_output['message']['from']['id'];
	}

	/**
     * @return int
     */
	public function getChatId() {
		return $this->_output['message']['chat']['id'];
	}

	/**
     * @return string
     */
	public function getText()
	{
		return isset($this->_output['message']['text']) ? $this->_output['message']['text'] : null;
	}

	/**
     * @return int
     */
	public function getMessageId()
	{
		return $this->_output['message']['message_id'];
	}

	/**
     * @return string
     */
	public function getChatType()
	{
		return $this->_output['message']['chat']['type'];
	}

	/**
     * @return string
     */
	public function getChatTitle()
	{
		return $this->_output['message']['chat']['title'];
	}
}

?>