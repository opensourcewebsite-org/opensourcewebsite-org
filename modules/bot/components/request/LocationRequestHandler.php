<?php
namespace app\modules\bot\components\request;

use TelegramBot\Api\Types\Update;

class LocationRequestHandler implements IRequestHandler
{
    public function getFrom(Update $update)
    {
        if (($message = $update->getMessage()) && $message->getLocation()) {
            $from = $message->getFrom();
        }

        return $from ?? null;
    }

    public function getChat(Update $update)
    {
        if (($message = $update->getMessage()) && $message->getLocation()) {
            $chat = $message->getChat();
        }

        return $chat ?? null;
    }

    public function getCommandText(Update $update)
    {
        if (($message = $update->getMessage()) && $message->getLocation()) {
            $commandText = '/update_location';
        }

        return $commandText ?? null;
    }
}
