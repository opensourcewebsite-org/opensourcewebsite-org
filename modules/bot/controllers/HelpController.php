<?php

namespace app\modules\bot\controllers;
use \app\modules\bot\components\response\SendMessageCommandSender;
use \app\modules\bot\components\response\commands\SendMessageCommand;

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
    	$update = $this->getUpdate();

		return [
			new SendMessageCommandSender(
				new SendMessageCommand([
					'chatId' => $update->getMessage()->getChat()->getId(),
					'parseMode' => $this->textFormat,
					'text' => $this->render('index'),
				])
			),
		];
    }
}
