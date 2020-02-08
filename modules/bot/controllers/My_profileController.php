<?php

namespace app\modules\bot\controllers;

use \app\modules\bot\components\response\SendMessageCommandSender;
use \app\modules\bot\components\response\commands\SendMessageCommand;

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

    	$text = $this->render('index', [
    		'profile' => $update->getMessage()->getFrom(),
    	]);

		return [
			new SendMessageCommandSender(
				new SendMessageCommand([
					'chatId' => $update->getMessage()->getChat()->getId(),
					'parseMode' => 'html',
					'text' => $this->prepareText($text),
				])
			),
		];
    }
}
