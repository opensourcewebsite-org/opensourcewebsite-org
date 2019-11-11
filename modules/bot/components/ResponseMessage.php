<?php

namespace app\modules\bot\components;

/**
 * Class ResponseMessage
 *
 * @package app\modules\bot\components
 */
class ResponseMessage extends RequestMessage
{

    /**
     * @var \TelegramBot\Api\Types\ReplyKeyboardHide|
     * \TelegramBot\Api\Types\ReplyKeyboardMarkup|
     * \TelegramBot\Api\Types\ForceReply|
     * \TelegramBot\Api\Types\ReplyKeyboardRemove|
     * null
     */
    protected $_keyboard = null;

    /**
     * @param \TelegramBot\Api\Types\ReplyKeyboardHide|
     * \TelegramBot\Api\Types\ReplyKeyboardMarkup|
     * \TelegramBot\Api\Types\ForceReply|
     * \TelegramBot\Api\Types\ReplyKeyboardRemove|
     * null $keyboard
     */
    public function setKeyboard($keyboard)
    {
        $this->_keyboard = $keyboard;
    }

    /**
     * @return \TelegramBot\Api\Types\ReplyKeyboardHide|
     * \TelegramBot\Api\Types\ReplyKeyboardMarkup|
     * \TelegramBot\Api\Types\ForceReply|
     * \TelegramBot\Api\Types\ReplyKeyboardRemove|
     * null $keyboard
     */
    public function getKeyboard()
    {
        return $this->_keyboard;
    }
}