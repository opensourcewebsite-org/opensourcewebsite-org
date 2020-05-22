<?php

namespace app\modules\bot\components\request;

use TelegramBot\Api\Types\Update;
use app\modules\bot\controllers\publics\TopController;

class RatingCommandResolver implements ICommandResolver
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
                $estimate = trim($commandText);
                if (in_array($estimate, ['+','-'])) {
                    $route = TopController::createRoute('start', ['estimate' => $estimate]);
                }
            }
        }
        return $route ?? null;
    }
}
