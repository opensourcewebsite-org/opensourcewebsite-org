<?php

namespace app\modules\bot\controllers;

use app\modules\bot\components\CommandController as Controller;

/**
 * Class DefaultController
 *
 * @package app\modules\bot\controllers
 */
class DefaultController extends Controller
{
    /**
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('/help/index');
    }

    /**
     * @return string
     */
    public function actionCommandNotFound()
	{
		return $this->render('command-not-found');
    }
}
