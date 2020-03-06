<?php
namespace app\modules\bot\components\request;

use TelegramBot\Api\Types\Update;

class SystemMessageRequestHandler extends MessageRequestHandler
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

    public function getCommandText(Update $update)
    {
        if ($message = $update->getMessage() && ($update->getMessage()->getNewChatMember() || $update->getMessage()->getLeftChatMember())) {
            $commandText = '/system_message';
        }

        return $commandText ?? null;
    }
}
