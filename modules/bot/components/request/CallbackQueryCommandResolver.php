<?php

namespace app\modules\bot\components\request;

use TelegramBot\Api\Types\Update;

class CallbackQueryCommandResolver implements ICommandResolver
{
    public function resolveCommand(Update $update)
    {
        if ($callbackQuery = $update->getCallbackQuery()) {
            $commandText = $callbackQuery->getData();
        }

        return $commandText ?? null;
    }
}
