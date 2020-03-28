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

    public function resolveRoute(Update $update, ?string $state, string $defaultRoute)
    {
        $params = [];

        foreach ($this->requestHandlers as $requestHandler) {
            $commandText = $requestHandler->getCommandText($update);
            if (isset($commandText)) {
                list($route, $params) = $this->resolveCommandRoute($commandText);

                if (!isset($route) && $commandText[0] == '/') {
                    list($route, $params) = [ $defaultRoute, [] ];
                }

                break;
            }
        }

        if (!isset($route) && !empty($state)) {
            list($route, $params) = $this->resolveCommandRoute($state);
        }

        return [ $route, $params ];
    }

    /**
     * Resolve route using list of aliases
     *
     * @param string $alias
     * @return array
     */
    private function resolveCommandRoute(string $alias)
    {
        $route = null;
        $params = [];

        foreach ($this->rules as $pattern => $targetRoute) {
            $pattern = $this->preparePattern($pattern);
            if (preg_match($pattern, $alias, $matches)) {
                list($route, $params) = $this->prepareRoute($targetRoute, $matches);
            }

            if (isset($route)) {
                break;
            }
        }

        return [$route, $params];
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

        $pattern = "#^$pattern$#u";
        foreach ($placeholders as $name => $expression) {
            $pattern = str_replace("<<" . $name . ">>", $expression, $pattern);
        }

        return $pattern;
    }

    /**
     * Dispatch params and convert route rule to ready route
     *
     * @param string $route
     * @param array $matches
     * @return array
     */
    private function prepareRoute(string $route, array $matches)
    {
        $namedGroups = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
        foreach ($namedGroups as $key => $value) {
            $token = "<$key>";
            if (stripos($route, $token) !== false) {
                if ($key == 'controller' || $key == 'action') {
                    $value = str_replace('_', '-', $value);
                }
                if ($key == 'action' && empty($value)) {
                    $value = 'index';
                }
                $route = str_replace($token, $value, $route);
                unset($namedGroups[$key]);
            }
        }


        $queryParams = [];
        if (array_key_exists('query', $namedGroups)) {
            $query = $namedGroups['query'];
            unset($namedGroups['query']);
            $queryParams = $this->parseQuery($query);
        }
        $params = array_merge($queryParams, $namedGroups);

        return [ $route, $params ];
    }

    /**
     * Parse query string to associative array of params
     *
     * @param string $query
     * @return array
     */
    private function parseQuery(string $query = '')
    {
        $params = [];

        if ($query) {
            $paramsKeyValues = explode('&', $query);
            foreach ($paramsKeyValues as $keyValue) {
                list($key, $value) = explode('=', $keyValue);
                $params[$key] = $value;
            }
        }

        return $params;
    }
}
