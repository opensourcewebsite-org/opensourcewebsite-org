<?php

namespace app\modules\bot\controllers;

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
		return [
			[
				'type' => 'message',
				'text' => $this->render('index'),
			]
		];
    }
}
