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
class PrivateRouteResolver extends Component
{
    /**
     * @var string
     */
    public $defaultRoute = 'message/index';

    /**
     * @var array
     */
    public $rules = [];

    /**
     * @var array
     */
    public $controllers = [];

    /**
     * @var array
     */
    public $actions = [];

    public function resolveRoute(Update $update, ?string $state)
    {
        $commandText = null;
        $route = null;
        $params = [];
        $isStateRoute = false;

        if ($callbackQuery = $update->getCallbackQuery()) {
            $commandText = $callbackQuery->getData();
        } elseif ($requestMessage = $update->getRequestMessage()) {
            if ($forwardFromUser = $requestMessage->getForwardFrom()) {
                // show user by forward message
                $route = 'user/message';
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
                    // replace short codes of controllers/actions to names
                    if (is_numeric($value)) {
                        if ($key == 'controller') {
                            $value = $this->controllers[(int)$value] ?? $value;
                        } elseif ($key == 'action') {
                            $value = $this->actions[(int)$value] ?? $value;
                        }
                    }

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

        return [$route, $params];
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
                $params[$key] = urldecode($value);
            }
        }

        return $params;
    }
}
