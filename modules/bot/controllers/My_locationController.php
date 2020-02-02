<?php

namespace app\modules\bot\controllers;

use app\modules\bot\components\CommandController as Controller;

/**
 * Class My_locationController
 *
 * @package app\modules\bot\controllers
 */
class My_locationController extends Controller
{
    /**
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }
}
