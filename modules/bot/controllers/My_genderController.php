<?php

namespace app\modules\bot\controllers;

/**
 * Class My_genderController
 *
 * @package app\modules\bot\controllers
 */
class My_genderController extends Controller
{
    /**
     * @return string
     */
    public function actionIndex()
    {
    	return [
            [
                'type' => 'message',
                'text' => $this->render('index'),
            ]
        ];
    }
}
