<?php

namespace app\modules\bot\components\request;

use TelegramBot\Api\Types\Update;
use app\modules\bot\models\BotCommandAlias;

class AliasCommandResolver implements ICommandResolver
{
    public function resolveCommand(Update $update)
    {
        $message = $update->getMessage();
        if (!$message) {
            $message = $update->getEditedMessage();
        }
        if ($message) {
            $replyMessage = $message->getReplyToMessage();
            if ($replyMessage) {
                $commandText = $message->getText();
                $commandAlias = BotCommandAlias::find()->where(['text' => $commandText])->one();
                if ($commandAlias) {
                    $route = $commandAlias->command;
                }
            }
        }
        return $route ?? null;
    }
}
