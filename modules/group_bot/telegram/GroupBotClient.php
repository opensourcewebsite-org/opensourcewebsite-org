<?php

namespace app\modules\group_bot\telegram;

use TelegramBot\Api\BotApi;
use Yii;

/**
 * Class GroupBotClient
 *
 * @package app\modules\group_bot\telegram
 */
class GroupBotClient extends BotApi
{
    /**
     * Telegram request
     */
    protected $_request;

    public function __construct($token, $request, $trackerToken = null)
    {
        parent::__construct($token, $trackerToken);

        $this->_request = $request;
    }

    /**
     * @return null|array
     */
    public function getCallbackQuery()
    {
        return isset($this->_request['callback_query']) ? $this->_request['callback_query'] : null;
    }

    /**
     * @return null|\TelegramBot\Api\Types\Message
     */
    public function getMessage()
    {
        $request = null;
        if (isset($this->_request['message'])) {
            $request = $this->_request['message'];
        } elseif (isset($this->_request['edited_message'])) {
            $request = $this->_request['edited_message'];
        }

        return $request ? \TelegramBot\Api\Types\Message::fromResponse($request) : null;
    }
}
