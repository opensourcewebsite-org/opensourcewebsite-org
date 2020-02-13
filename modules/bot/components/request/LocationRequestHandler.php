<?php
namespace app\modules\bot\components\request;

use TelegramBot\Api\Types\Update;

class LocationRequestHandler implements IRequestHandler
{
    public function getFrom(Update $update)
    {
        if ($message = $update->getMessage()) {
            $from = $message->getFrom();
        }

        return $from;
    }

    public function getCommandText(Update $update)
    {
        if (($message = $update->getMessage()) && $message->getLocation()) {
            $commandText = '/update_location';
        }

        return $commandText;
    }
}
