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
     * @param array  $params
     */
    public function set($actionName, $params)
    {
        $this->state->setIntermediateField(
            'back-route',
            $this->controller::createRoute($actionName, $params)
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
