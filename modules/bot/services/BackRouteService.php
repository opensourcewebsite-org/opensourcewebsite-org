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
            'back-route',
            $this->controller::createRoute($actionName, $params)
        );
    }

    /**
     * @param string $route
     */
    public function set($route)
    {
        $this->state->setIntermediateField(
            'back-route',
            $route
        );
    }

    /**
     * @return string
     */
    public function get()
    {
        return $this->state->getIntermediateField('back-route');
    }
}
