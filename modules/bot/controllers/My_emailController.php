<?php

namespace app\modules\bot\controllers;

use app\modules\bot\components\CommandController as Controller;

/**
 * Class My_emailController
 *
 * @package app\modules\bot\controllers
 */
class My_emailController extends Controller
{
    /**
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }
}
