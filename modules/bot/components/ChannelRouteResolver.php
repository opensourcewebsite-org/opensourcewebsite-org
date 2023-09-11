<?php

namespace app\modules\bot\components;

use app\modules\bot\components\api\Types\Update;
use Yii;
use yii\base\Component;

/**
 * Class ChannelRouteResolver
 *
 * @package app\modules\bot\components
 */
class ChannelRouteResolver extends RouteResolver
{
    public function resolveRoute(Update $update, ?string $state = null)
    {
        $commandText = null;
        $route = null;
        $params = [];
        $isStateRoute = false;

        if ($callbackQuery = $update->getCallbackQuery()) {
            $commandText = $callbackQuery->getData();
        } else {
            $requestMessage = $update->getRequestMessage();
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
