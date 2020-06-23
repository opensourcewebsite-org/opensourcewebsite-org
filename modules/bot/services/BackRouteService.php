<?php


namespace app\modules\bot\services;

use app\modules\bot\components\Controller;
use app\modules\bot\models\UserState;

/**
 * Class BackRouteService
 *
 * @package app\modules\bot\services
 */
class BackRouteService
{
    const ROUTE_NAME = 'back-route';

    /** @var UserState */
    public $state;
    /** @var Controller */
    public $controller;

    /**
     * @param string $actionName
     * @param array $params
     */
    public function make($actionName, $params)
    {
        $this->state->setIntermediateField(
            self::ROUTE_NAME,
            $this->controller::createRoute($actionName, $params)
        );
    }

    /**
     * @param string $route
     */
    public function set($route)
    {
        $this->state->setIntermediateField(
            self::ROUTE_NAME,
            $route
        );
    }

    /**
     * @return string
     */
    public function get()
    {
        return $this->state->getIntermediateField(self::ROUTE_NAME);
    }
}
