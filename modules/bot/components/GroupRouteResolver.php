<?php

namespace app\modules\bot\components;

use app\modules\bot\components\api\Types\Update;
use app\modules\bot\controllers\groups\SystemMessageController;
use Yii;
use yii\base\Component;

/**
 * Class GroupRouteResolver
 *
 * @package app\modules\bot\components
 */
class GroupRouteResolver extends RouteResolver
{
    public function resolveRoute(Update $update, ?string $state = null)
    {
        $commandText = null;
        $route = null;
        $params = [];
        $isStateRoute = false;

        if ($callbackQuery = $update->getCallbackQuery()) {
            $commandText = $callbackQuery->getData();
        } elseif ($requestMessage = $update->getMessage()) {
            if ($requestMessage->getNewChatMembers()) {
                $commandText = SystemMessageController::createRoute('new-chat-members');
            } elseif ($requestMessage->getLeftChatMember()) {
                $commandText = SystemMessageController::createRoute('left-chat-member');
            } elseif ($requestMessage->getMigrateToChatId()) {
                $commandText = SystemMessageController::createRoute('group-to-supergroup');
            } elseif ($requestMessage->getVideoChatScheduled()) {
                $commandText = SystemMessageController::createRoute('video-chat-scheduled');
            } elseif ($requestMessage->getVideoChatStarted()) {
                $commandText = SystemMessageController::createRoute('video-chat-started');
            } elseif ($requestMessage->getVideoChatEnded()) {
                $commandText = SystemMessageController::createRoute('video-chat-ended');
            } elseif ($requestMessage->getVideoChatParticipantsInvited()) {
                $commandText = SystemMessageController::createRoute('video-chat-participants-invited');
            }
        } else {
            $requestMessage = $update->getEditedMessage();
        }

        if (isset($requestMessage) && !isset($commandText)) {
            $commandText = $requestMessage->getText();
        }

        if (isset($commandText)) {
            list($route, $params) = $this->resolveCommandRoute($commandText);
        }

        if (!isset($route) && !empty($state)) {
            list($route, $params) = $this->resolveCommandRoute($state);

            if (isset($route) && isset($commandText)) {
                $params['text'] = $commandText;
            }

            $isStateRoute = true;
        }

        if (!isset($route)) {
            $route = $this->defaultRoute;
        }

        $commandText ? Yii::warning('Input: ' . $commandText) : null;
        $route ? Yii::warning('Route: ' . $route) : null;
        $state ? Yii::warning('State: ' . $state) : null;

        return [$route, $params, $isStateRoute];
    }
}
