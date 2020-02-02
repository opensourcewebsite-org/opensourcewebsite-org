<?php

namespace app\modules\bot\controllers;

use app\modules\bot\components\CommandController as Controller;

/**
 * Class CommandNotFoundController
 *
 * @package app\modules\bot\controllers
 */
class CommandNotFoundController extends Controller
{
    /**
     * @return string
     */
    public function actionIndex()
	{
		return $this->render('index');
    }
}
