<?php

namespace app\modules\bot\controllers;

use Yii;
use app\modules\bot\components\CommandController as Controller;

/**
 * Class ReverseController
 *
 * @package app\modules\bot\controllers
 */
class ReverseController extends Controller
{
    /**
     * @return string
     */
    public function actionIndex($message = '')
    {
        //TODO add reverse for $$message
        return $message ? $message : '';

        //return $this->render('index');
    }
}
