<?php

namespace app\modules\bot\controllers;

use app\modules\bot\CommandController;

class ExempleController extends CommandController {

    public function actionCommandNotFound() {
        return 'Command not found!';
    }
}