<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\Controller as Controller;
use app\modules\bot\components\response\SendMessageCommand;

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
        return [
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('/help/index'),
                [
                    'parseMode' => $this->textFormat,
                ]
            ),
        ];
    }

    /**
     * @return string
     */
    public function actionCommandNotFound()
	{
        return [
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('command-not-found'),
                [
                    'parseMode' => $this->textFormat,
                ]
            ),
        ];
    }
}
