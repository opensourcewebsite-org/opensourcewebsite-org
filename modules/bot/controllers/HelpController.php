<?php

namespace app\modules\bot\controllers;

/**
 * Class HelpController
 *
 * @package app\controllers\bot
 */
class HelpController extends Controller
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
