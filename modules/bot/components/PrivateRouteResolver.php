<?php

namespace app\modules\bot\components;

use app\modules\bot\components\api\Types\Update;
use Yii;
use yii\base\Component;

/**
 * Class PrivateRouteResolver
 *
 * @package app\modules\bot\components
 */
class PrivateRouteResolver extends RouteResolver
{
    public function resolveRoute(Update $update, ?string $state = null)
    {
        $commandText = null;
        $route = null;
        $params = [];
        $isStateRoute = false;

        if ($callbackQuery = $update->getCallbackQuery()) {
            $commandText = $callbackQuery->getData();
        } elseif ($requestMessage = $update->getRequestMessage()) {
            if ($forwardFrom = $requestMessage->getForwardFrom()) {
                // show user by forward message
                $route = 'user/message';
            } elseif ($contact = $requestMessage->getContact()) {
                if ($userId = $contact->getUserId()) {
                    // show user by contact
                    $route = 'user/id';
                    $params['id'] = $userId;
                }
            } else {
                $commandText = $requestMessage->getText();

                if (empty($state) && $commandText) {
                    if ($commandText[0] == '@') {
                        // show user by telegram username
                        $route = 'user/username';
                    } elseif ((int)$commandText[0] > 0) {
                        // show user by telegram id
                        $route = 'user/id';
                    }
                }
            }
        }

        if (!isset($route) && isset($commandText)) {
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
