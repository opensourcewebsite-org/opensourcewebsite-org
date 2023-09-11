<?php

namespace app\modules\bot\components;

use app\modules\bot\components\api\Types\Update;
use phpseclib3\Crypt\DSA\PrivateKey;
use Yii;
use yii\base\Component;

/**
 * Class RouteResolver
 *
 * @package app\modules\bot\components
 */
abstract class RouteResolver extends Component
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

    abstract public function resolveRoute(Update $update, ?string $state = null);

    /**
     * Resolve route using list of aliases
     *
     * @param string $alias
     * @return array
     */
    protected function resolveCommandRoute(string $alias)
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

        if ($params) {
            Yii::$app->request->setQueryParams($params);
        }

        return [$route, $params];
    }

    /**
     * Convert rule syntax to regular expression with placeholders
     *
     * @param $pattern
     * @return mixed|string
     */
    protected function preparePattern($pattern)
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
    protected function prepareRoute(string $route, array $matches)
    {
        $namedGroups = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

        foreach ($namedGroups as $key => $value) {
            $token = "<$key>";

            if (stripos($route, $token) !== false) {
                if (($key == 'controller') || ($key == 'action')) {
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
    protected function parseQuery(string $query = '')
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
