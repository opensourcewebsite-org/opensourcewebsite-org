<?php

namespace app\modules\bot;

use Yii;
use yii\base\Action;

/**
 * Class WebHookAction
 *
 * @package app\modules\bot
 */
class WebHookAction extends Action
{
    /**
     * @param string $token
     * @return bool
     */
    public function run($token = '')
    {
        $input = file_get_contents('php://input');
        $module =  Yii::$app->getModule('bot');

        return $module->handleInput($input, $token);
    }
}
