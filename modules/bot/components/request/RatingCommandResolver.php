<?php

namespace app\modules\bot\components\request;

use TelegramBot\Api\Types\Update;
use  app\modules\bot\controllers\publics\RatingController;

class RatingCommandResolver implements ICommandResolver
{
    public function resolveCommand(Update $update)
    {
        $message = $update->getMessage();
        $replyMessage = $message->getReplyToMessage();
        if ($replyMessage) {
            $commandText = $message->getText();
            $estimate = trim($commandText);
            if (in_array($estimate, ['+','-'])) {
                $route = RatingController::createRoute('index', ['estimate' => $estimate]);
            }
        }
        return $route ?? null;
    }
}
