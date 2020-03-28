<?php


namespace app\modules\bot\components\request;


use TelegramBot\Api\Types\Update;

class MessageCommandResolver implements  ICommandResolver
{
    public function resolveCommand(Update $update)
    {
        if ($message = $update->getMessage()) {
            $commandText = $message->getText();
        }

        return $commandText ?? null;
    }
}
