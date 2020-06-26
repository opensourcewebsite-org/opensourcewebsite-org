<?php

namespace app\modules\bot\components\request;

use yii\base\Component;

class Request extends Component
{
    /**
     * @var string
     */
    private $controller;

    /**
     * @var string
     */
    private $action;

    /**
     * @var array
     */
    private $params;

    /**
     * @param string $route
     * @param array $params
     * @return Request
     */
    public static function fromRouteAndParams(string $route, array $params)
    {
        list($controller, $action) = explode('/', $route);
        $request = new Request();
        $request->controller = $controller;
        $request->action = $action;
        $request->params = $params;
        return $request;
    }

    public static function fromUrl(string $url)
    {
        list($route, $query) = explode('?', $url);
        $params = [];
        if ($query) {
            $paramsKeyValues = explode('&', $query);
            foreach ($paramsKeyValues as $keyValue) {
                list($key, $value) = explode('=', $keyValue);
                $params[$key] = $value;
            }
        }
        return self::fromRouteAndParams($route, $params);
    }

    /**
     * @return string
     */
    public function getRoute()
    {
        return "{$this->controller}/{$this->action}";
    }

    /**
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param string $name
     * @param $defaultValue
     * @return mixed
     */
    public function getParam(string $name, $defaultValue)
    {
        return $this->params[$name] ?? $defaultValue;
    }
}
