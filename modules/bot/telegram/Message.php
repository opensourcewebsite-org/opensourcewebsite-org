<?php

namespace app\modules\bot\telegram;

use TelegramBot\Api\Types\Message as TelegramMessageType;

/**
 * Class Message
 *
 * @package app\modules\bot\telegram
 */
class Message extends TelegramMessageType
{
    /**
     * @return bool
     */
    public function isBotCommand()
    {
        return (substr(trim($this->getText()), 0, 1) === '/');
    }

    /**
     * @param string $text
     *
     * @return string
     */
    public static function prepareText($text)
    {
        $text = str_replace(["\n", "\r\n"], '', $text);

        return preg_replace('/<br\W*?\/>/i', PHP_EOL, $text);
    }
}