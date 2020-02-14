<?php

namespace app\modules\bot\components\message;

use app\models\BotMessageFilter;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\ChatMember;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\Update;

/**
 * Class MessageHandler
 *
 * @package app\modules\bot\telegram
 */
class MessageHandler
{
    /**
     * @var Message
     */
    private $_message = null;
    private $_api = null;
    private $_is_admin_message = false;

    /**
     * MessageHandler constructor.
     *
     * @param BotApi $botApi
     * @param Message|null $message
     */
    public function __construct(BotApi $botApi, Message $message = null)
    {
        $this->_api = $botApi;
        $this->_message = $message;

        if ($message) {
            $this->setMessage($message);
        }
    }

    /**
     * general message filter method
     */
    public function filterMessage()
    {
        $this->filterByWord();
    }

    /**
     * @return bool
     */
    public function isAdminMessage(): bool
    {
        $chatId = $this->getMessage()->getChat()->getId();
        $administrators = $this->_api->getChatAdministrators($chatId);

        /**
         * @var $user ChatMember
         */
        foreach ($administrators as $user) {
            if ($this->getMessage()->getFrom()->getId() == $user->getUser()->getId()) {
                $this->_is_admin_message = true;
            }
        }

        return $this->_is_admin_message;
    }

    /**
     * @param bool $is_admin_message
     */
    public function setIsAdminMessage(bool $is_admin_message): void
    {
        $this->_is_admin_message = $is_admin_message;
    }

    /**
     * filter message by word
     */
    private function filterByWord()
    {
        $pattern = BotMessageFilter::getFilterPattern();

        if ($this->isWordInMessage($pattern) && !$this->isAdminMessage()) {
            $messageId = $this->getMessage()->getMessageId();
            $chatId = $this->getMessage()->getChat()->getId();
            $this->_api->deleteMessage($chatId, $messageId);
        }
    }

    /**
     * @return Message
     */
    public function getMessage(): Message
    {
        return $this->_message;
    }

    /**
     * @param Message $message
     */
    public function setMessage(Message $message): void
    {
        $this->_message = $message;
    }

    /**
     * @param $pattern - eg (word1|word2)
     *
     * @return bool
     */
    private function isWordInMessage($pattern)
    {
        $pattern = "/^.*$pattern.*\$/mi";
        $message = $this->getMessage()->getText();

        if (preg_match_all($pattern, $message, $matches)) {
            return true;
        }

        return false;
    }
}
