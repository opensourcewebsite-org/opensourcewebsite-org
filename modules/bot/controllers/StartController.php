<?php

namespace app\modules\bot\controllers;

use app\modules\bot\CommandController;

/**
 * Class StartController
 *
 * @package app\controllers\bot
 */
class StartController extends CommandController
{

    /**
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }
}