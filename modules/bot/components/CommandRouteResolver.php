<?php

namespace app\modules\bot\components;

use TelegramBot\Api\Types\Update;
use Yii;
use yii\base\Component;

/**
 * Class CommandRouter
 *
 * @package app\modules\bot\components
 */
class CommandRouteResolver extends Component
{
    /**
     * @var array
     */
    public $requestHandlers = [];

    /**
     * @var array
     */
    public $rules = [];

    public function resolveRoute(Update $update, ?string $state)
    {
        $params = null;

        foreach ($this->requestHandlers as $requestHandler) {
            $commandText = $requestHandler->getCommandText($update);
            if (isset($commandText)) {
                list($route, $params) = $this->resolveCommandRouteFromAlias($commandText);
                if (!isset($route)) {
                    list($route, $params) = $this->resolveCommandRouteFromUrl($commandText);
                }
                break;
            }
        }

        if (!isset($route) && !empty($state)) {
            list($route, $params) = $this->resolveCommandRouteFromAlias($state);
            if (!isset($route)) {
                list($route, $params) = $this->resolveCommandRouteFromUrl($state);
            }
        }

        if (!isset($route)) {
            $route = 'default/command-not-found';
        }

        Yii::warning($route);

        return [ $route, $params ];
    }

    /**
     * Resolve route using list of aliases
     *
     * @param string $alias
     * @return array
     */
    private function resolveCommandRouteFromAlias(string $alias)
    {
        $route = null;
        $params = [];

        foreach ($this->rules as $pattern => $targetRoute) {
            $pattern = $this->preparePattern($pattern);

            if (preg_match($pattern, $alias, $matches)) {
                list($route, $params) = $this->prepareRoute($targetRoute, $matches);
            }

            if ($route) {
                break;
            }
        }

        return [$route, $params];
    }

    /**
     * Resolve route parsing url
     *
     * @param string $url
     * @return array
     */
    private function resolveCommandRouteFromUrl(string $url)
    {
        $isValidUrl = preg_match('#\w+/\w+(\?(\w+=.*))?#', $url);
        if ($isValidUrl) {
            $params = [];
            list($route, $paramsString) = explode('?', $url);
            $paramsKeyValues = explode('&', $paramsString);
            foreach ($paramsKeyValues as $keyValue) {
                list($key, $value) = explode('=', $keyValue);
                $params[$key] = $value;
            }
            return [$route, $params];
        }
    }

    /**
     * Convert rule syntax to regular expression with placeholders
     *
     * @param $pattern
     *
     * @return mixed|string
     */
    private function preparePattern($pattern)
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
    private function prepareRoute($targetRoute, $matches)
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
