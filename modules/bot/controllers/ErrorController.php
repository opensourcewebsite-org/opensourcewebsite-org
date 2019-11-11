<?php

namespace app\modules\bot\controllers;

use app\modules\bot\CommandController;

/**
 * Class ExempleController
 *
 * @package app\modules\bot\controllers
 */
class ErrorController extends CommandController
{

    /**
     * @return string
     */
    public function actionCommandNotFound() {
        return 'Command not found!';
    }
}