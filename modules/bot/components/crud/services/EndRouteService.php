<?php

namespace app\modules\bot\components\crud\services;

use app\modules\bot\components\Controller;
use app\modules\bot\models\UserState;

/**
 * Class EndRouteService
 *
 * @package app\modules\bot\services
 */
class EndRouteService
{
    public const ROUTE_NAME = 'end-route';

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
        $this->state->setItem(
            self::ROUTE_NAME,
            $this->controller::createRoute($actionName, $params)
        );
    }

    /**
     * @param string $route
     */
    public function set($route)
    {
        $this->state->setItem(
            self::ROUTE_NAME,
            $route
        );
    }

    /**
     * @return string
     */
    public function get()
    {
        return $this->state->getItem(self::ROUTE_NAME);
    }
}
