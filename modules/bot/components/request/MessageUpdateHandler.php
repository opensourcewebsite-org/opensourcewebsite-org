<?php
namespace app\modules\bot\components\request;

use TelegramBot\Api\Types\Update;

class MessageUpdateHandler implements IUpdateHandler
{
    public function getFrom(Update $update)
    {
        if ($message = $update->getMessage()) {
            $from = $message->getFrom();
        }

        return $from ?? null;
    }

    public function getChat(Update $update)
    {
        if ($message = $update->getMessage()) {
            $chat = $message->getChat();
        }
        return $chat ?? null;
    }
}
