<?php

namespace app\modules\bot\components\request;

use Yii;
use TelegramBot\Api\Types\Update;
use app\modules\bot\models\BotRouteAlias;
use app\modules\bot\controllers\publics\VotebanController;
use app\modules\bot\controllers\publics\TopController;
use app\modules\bot\components\helpers\Emoji;

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
            'voteban' => VotebanController::createRoute('index'),
            '+' => TopController::createRoute('start-like'),
            Emoji::LIKE => TopController::createRoute('start-like'),
            '-' => TopController::createRoute('start-dislike'),
            Emoji::DISLIKE => TopController::createRoute('start-dislike'),
        ];

        foreach ($aliases as $alias => $aliasRoute) {
            if ($alias === $text) {
                $route = $aliasRoute;
            }
        }

        return $route ?? null;
    }

    private function getGroupAlias($text)
    {
        if ($text) {
            $routeAlias = BotRouteAlias::find()
                ->where([
                    'text' => $text,
                ])
                ->one();

            if ($routeAlias) {
                $route = $routeAlias->route;
            }
        }

        return $route ?? null;
    }
}
