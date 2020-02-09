<?php

namespace app\modules\bot\controllers;

use \app\modules\bot\components\response\SendMessageCommand;

/**
 * Class My_profileController
 *
 * @package app\modules\bot\controllers
 */
class My_profileController extends Controller
{
    /**
     * @return string
     */
    public function actionIndex()
    {
    	$update = $this->getUpdate();

		return [
			new SendMessageCommand(
                $update->getMessage()->getChat()->getId(),
                $this->render('index', [
                    'profile' => $update->getMessage()->getFrom(),
                ]),
				[
                    'parseMode' => $this->textFormat,
				]
			),
		];
    }
}
