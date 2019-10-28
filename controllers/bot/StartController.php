<?php

namespace app\controllers\bot;

use app\components\BotCommandController;

/**
 * Class StartController
 *
 * @package app\controllers\bot
 */
class StartController extends BotCommandController
{

    /**
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }
}