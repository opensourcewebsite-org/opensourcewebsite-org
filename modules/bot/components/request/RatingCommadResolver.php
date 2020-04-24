<?php

namespace app\modules\bot\components\request;

use TelegramBot\Api\Types\Update;

class RatingCommandResolver implements ICommandResolver
{
    public function resolveCommand(Update $update)
    {
        if ($message = $update->getMessage()) {
            $commandText = $message->getText();
        }
        \Yii::warning($commandText.' - TEST', 'xxxxx');
        return $commandText ?? null;
    }
}
