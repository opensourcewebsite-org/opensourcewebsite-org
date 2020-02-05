<?php

namespace app\modules\group_bot\models;

/**
 * Class CallbackQuery
 *
 * @package app\modules\group_bot\models
 */
class CallbackQuery {
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
	public function getChatId()
	{
		return $this->_output['callback_query']['message']['chat']['id'];
	}

	/**
     * @return int
     */
	public function getMid()
	{
		return $this->_output['callback_query']['message']['message_id'];
	}

	/**
     * @return string
     */
	public function getData()
	{
		return $this->_output['callback_query']['data'];
	}
}

?>