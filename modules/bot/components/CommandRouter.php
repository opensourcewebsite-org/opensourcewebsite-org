<?php

namespace app\modules\bot\components;

use app\modules\bot\telegram\BotApiClient;
use app\modules\bot\Module;
use yii\base\Component;
use yii\base\InvalidRouteException;

/**
 * Class CommandRouter
 *
 * @package app\modules\bot\components
 */
class CommandRouter extends Component
{
    /**
     * @var string
     */
    public $defaultAction = 'index';

    public $invalidRouteRedirect = false;

    /**
     * @var array
     */
    public $controllerMap = [];

    /**
     * @var string
     */
    public $controllerNamespace = 'app\\controllers\\bot';

    /**
     * @var array
     */
    public $rules = [];

    /**
     * Check rules and if route is founded execute route action
     *
     * @param TelegramBot\Api\BotApi $botApi
     *
     * @return bool
     * @throws \yii\base\InvalidRouteException
     * @throws \yii\base\InvalidConfigException
     */
    public function dispatchRoute($update)
    {
        $status = false;
        $route = null;
        $params = [];
        $response = null;
        $notFound = false;

        $isBotCommand = false;

        if ($update->getMessage() && $this->isBotCommand($update->getMessage()->getText())) {
            $parts = $this->resolveCommandRoute($update->getMessage()->getText());
            list($route, $params) = $parts;
            $isBotCommand = true;
        } elseif ($callbackQuery = $update->getCallbackQuery()) {
            $parts = $this->resolveCallbackRoute($callbackQuery);
            list($route, $params) = $parts;
        } else {
            list($route, $params) = $this->resolveRouteByState(Module::getInstance()->botClient->getState());
        }

        if ($route) {
            \Yii::warning($route);
            \Yii::warning($params);
            try {
                $response = Module::getInstance()->runAction($route, $params);
            } catch (InvalidRouteException $e) {
                $notFound = true;
            }
        } elseif ($this->invalidRouteRedirect && $isBotCommand) {
            $notFound = true;
        }

        if ($notFound) {
            $response = Module::getInstance()->runAction($this->invalidRouteRedirect);
        }

        return $response;
    }

    public function isBotCommand($text)
    {
        return substr(trim($text), 0, 1) === '/';
    }

    /**
     * Resolve route in command rules
     *
     * @param $commandText
     *
     * @return array
     */
    public function resolveCommandRoute($commandText)
    {
        $route = null;
        $params = [];

        foreach ($this->rules as $pattern => $targetRoute) {
            if ('/' !== substr($pattern, 0, 1)) {
                continue;
            }

            $pattern = $this->preparePattern($pattern);

            if (preg_match($pattern, $commandText, $matches)) {
                list($route, $params) = $this->prepareRoute($targetRoute, $matches);
            }

            if ($route) {
                break;
            }
        }

        return [$route, $params];
    }

    /**
     * Resolve route in callback query rules
     *
     * @param $callbackQuery
     *
     * @return array
     */
    public function resolveCallbackRoute($callbackQuery)
    {
        $route = null;
        $params = [];

        $callbackText = '@' . $callbackQuery->getData();

        foreach ($this->rules as $pattern => $targetRoute) {
            if ('@' !== substr($pattern, 0, 1)) {
                continue;
            }

            $pattern = $this->preparePattern($pattern);

            if (preg_match($pattern, $callbackText, $matches)) {
                list($route, $params) = $this->prepareRoute($targetRoute, $matches);
            }

            if ($route) {
                break;
            }
        }

        return [$route, $params];
    }

    /**
     * Resolve route depending on state
     *
     * @param $state
     *
     * @return array
     */
    public function resolveRouteByState($state)
    {
        list($route, $params) = $this->resolveCommandRoute($state->state);
        return [ $route, [] ];
    }

    /**
     * Convert rule syntax to regular expression with placeholders
     *
     * @param $pattern
     *
     * @return mixed|string
     */
    public function preparePattern($pattern)
    {
        $placeholders = [];

        if (preg_match_all('/<([\w._-]+):?([^>]+)?>/', $pattern, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $name = $match[1][0];
                $patternPart = isset($match[2][0]) ? $match[2][0] : '[^\/]+';
                $placeholders[$name] = "(?P<" . $name . ">" . $patternPart . ")";
                $pattern = str_replace($match[0][0], "<<" . $name . ">>", $pattern);
            }
        }

        $tr = [
            '.' => '\\.',
            '*' => '\\*',
            '$' => '\\$',
            '[' => '\\[',
            ']' => '\\]',
            '(' => '\\(',
            ')' => '\\)',
        ];

        //$pattern = '#^' . $prefix . trim(strtr($pattern, $tr), '/@') . '$#u';
        $pattern = '#^' . strtr($pattern, $tr) . '$#u';
        foreach ($placeholders as $name => $expression) {
            $pattern = str_replace("<<" . $name . ">>", $expression, $pattern);
        }

        return $pattern;
    }

    /**
     * Dispatch params and convert route rule to ready route
     *
     * @param $targetRoute
     * @param $matches
     *
     * @return array
     */
    public function prepareRoute($targetRoute, $matches)
    {
        $route = $targetRoute;
        if (isset($matches['controller'])) {
            $route = str_replace('<controller>', $matches['controller'], $route);
            unset($matches['controller']);
        }
        if (isset($matches['action'])) {
            $route = str_replace('<action>', $matches['action'], $route);
            unset($matches['action']);
        }

        $params = array_filter($matches, function ($k) { return !is_numeric($k); }, ARRAY_FILTER_USE_KEY);

        return [$route, $params];
    }

}