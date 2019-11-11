<?php

namespace app\modules\bot\controllers;

use app\modules\bot\CommandController;

/**
 * Class HelpController
 *
 * @package app\controllers\bot
 */
class HelpController extends CommandController
{

    /**
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }
}