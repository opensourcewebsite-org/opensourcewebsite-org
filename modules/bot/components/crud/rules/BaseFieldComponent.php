<?php

namespace app\modules\bot\components\crud\rules;

use app\modules\bot\components\api\Types\Update;
use app\modules\bot\components\CrudController;
use app\modules\bot\models\User;
use app\modules\bot\models\UserState;

/**
 * Class BaseFieldComponent
 *
 * @package app\modules\bot\components\rules
 */
abstract class BaseFieldComponent
{
    /** @var CrudController */
    public $controller;
    /** @var array */
    public $config;

    /**
     * BaseFieldComponent constructor.
     *
     * @param $controller
     * @param $config
     */
    public function __construct($controller, $config)
    {
        $this->controller = $controller;
        $this->config = $config;
    }

    /**
     * @return Update
     */
    protected function getUpdate()
    {
        return $this->controller->getUpdate();
    }

    /**
     * @return UserState|null
     */
    protected function getState()
    {
        if (method_exists($this->controller, 'getState')
            && is_callable($$this->controller, 'getState')) {
            return $this->controller->getState();
        }

        return null;
    }

    /**
     * @return \app\modules\bot\models\User|null
     */
    protected function getTelegramUser()
    {
        if (method_exists($this->controller, 'getTelegramUser')
            && is_callable($this->controller, 'getTelegramUser')) {
            return $this->controller->getTelegramUser();
        }

        return null;
    }

    /**
     * @return User
     */
    protected function getUser()
    {
        return $this->controller->getUser();
    }
}
