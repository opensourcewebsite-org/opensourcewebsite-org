<?php

namespace app\modules\bot\components\actions;

use yii\base\Action;
use app\modules\bot\components\response\ResponseBuilder;
use app\modules\bot\components\api\Types\Update;

abstract class BaseAction extends Action
{
    /**
     * @return \app\modules\bot\models\UserState
     */
    protected function getState()
    {
        return $this->controller->module->userState;
    }

    /**
     * @return Update
     */
    protected function getUpdate()
    {
        return $this->controller->module->update;
    }

    /**
     * @return \app\modules\bot\components\response\ResponseBuilder
     */
    protected function getResponseBuilder()
    {
        return ResponseBuilder::fromUpdate($this->getUpdate());
    }

    /**
     * @param string $view
     * @param array $params
     * @return \app\modules\bot\components\helpers\MessageText
     * Instance of MessageText class that is used for sending Telegram commands
     */
    public function render($view, $params = [])
    {
        return $this->controller->render($view, $params);
    }

    /**
     * @return \app\modules\bot\models\User
     */
    protected function getTelegramUser()
    {
        return $this->controller->module->telegramUser;
    }

    public function createRoute(string $actionName = 'index', array $params = [])
    {
        $controllerClass = $this->controller->className();
        return $controllerClass::createRoute($actionName, $params);
    }
}
