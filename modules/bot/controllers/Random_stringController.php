<?php

namespace app\modules\bot\controllers;

use Yii;
use app\modules\bot\components\CommandController as Controller;

/**
 * Class Random_stringController
 *
 * @package app\modules\bot\controllers
 */
class Random_stringController extends Controller
{
    /**
     * @return string
     */
    public function actionIndex($message = '')
    {
        //TODO add flexible int $n (1-1024) from $message
        $n = 10;
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';

        for ($i = 0; $i < $n; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }

        return $randomString;
        //return $this->render('index');
    }
}
