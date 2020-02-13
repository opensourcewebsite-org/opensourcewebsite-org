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
     *
     * @return bool
     */
    public function run($token = '')
    {
        $result = false;
        try {
            $input = file_get_contents('php://input');
            $botModule =  Yii::$app->getModule('bot');
            $result = $botModule->handleInput($input, $token);
        } catch (\Exception $ex) {
            Yii::error($ex->getMessage());
        }

        return $result;
    }
}
