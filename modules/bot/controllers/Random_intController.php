<?php

namespace app\modules\bot\controllers;

use Yii;
use app\modules\bot\components\CommandController as Controller;

/**
 * Class Random_intController
 *
 * @package app\modules\bot\controllers
 */
class Random_intController extends Controller
{
    /**
     * @return string
     */
    public function actionIndex()
    {
        return random_int(1, 10);
        //return $this->render('index');
    }
}
