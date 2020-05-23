<?php

namespace app\modules\bot\components\request;

use TelegramBot\Api\Types\Update;
use app\modules\bot\models\BotRouteAlias;
use app\modules\bot\controllers\publics\VotebanController;

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
                $route = $this->getStaticAlias($commandText);
                $route = $route ?? $this->getGroupAlias($commandText);
            }
        }
        return $route ?? null;
    }

    private function getStaticAlias($text)
    {
        $aliases = [
            'voteban' => VotebanController::createRoute('index')
        ];

        return $aliases[$text] ?? null;
    }

    private function getGroupAlias($text)
    {
        if ($text) {
            $routeAlias = BotRouteAlias::find()->where(['text' => $text])->one();
        }
        if ($routeAlias) {
            $route = $routeAlias->route;
        }

        return $route ?? null;
    }
}
