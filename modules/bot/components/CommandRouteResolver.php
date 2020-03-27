<?php

namespace app\modules\bot\components;

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

    public function resolveRoute($update, $state)
    {
        $route = null;
        $params = null;
        $isStateRoute = false;

        foreach ($this->requestHandlers as $requestHandler) {
            $commandText = $requestHandler->getCommandText($update);
            if (isset($commandText)) {
                list($route, $params) = $this->resolveCommandRoute($commandText);
                if (isset($route)) {
                    break;
                }
            }
        }

        if (!isset($route) && !empty($state)) {
            list($route, $params) = $this->resolveCommandRoute($state);
            $isStateRoute = true;
        }

        if (!isset($route)) {
            $route = 'default/command-not-found';
        }

        return [ $route, $params, $isStateRoute ];
    }

    /**
     * Resolve route in command rules
     *
     * @param $commandText
     *
     * @return array
     */
    private function resolveCommandRoute($commandText)
    {
        $route = null;
        $params = [];

        foreach ($this->rules as $pattern => $targetRoute) {
            $pattern = $this->preparePattern($pattern);
            if (preg_match($pattern, $commandText, $matches)) {
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
     * @param $targetRoute
     * @param $matches
     *
     * @return array
     */
    private function prepareRoute($targetRoute, $matches)
    {
        $route = $targetRoute;

        $namedGroups = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
        foreach ($namedGroups as $key => $namedGroup) {
            $token = "<$key>";
            if (stripos($route, $token) !== false) {
                $route = str_replace($token, $namedGroup, $route);
                unset($namedGroups[$key]);
            }
        }
        $params = $namedGroups;

        return [$route, $params];
    }
}
