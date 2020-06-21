<?php


namespace app\modules\bot\components\rules;

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
    /** @var UserState */
    public $state;
    /** @var Update */
    public $update;
    /** @var  User */
    public $telegramUser;

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
        $this->state = $this->controller->getState();
        $this->update = $this->controller->module->update;
        $this->telegramUser = $this->controller->getTelegramUser();
    }
}
