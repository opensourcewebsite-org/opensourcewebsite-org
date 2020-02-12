<?php

namespace app\modules\bot\controllers;

use \app\modules\bot\components\response\SendMessageCommand;
use \app\modules\bot\components\ReplyKeyboardManager;

/**
 * Class StartController
 *
 * @package app\controllers\bot
 */
class StartController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        $update = $this->getUpdate();

        ReplyKeyboardManager::getInstance()->addKeyboardButton(0, [
            'text' => '⚙️',
            ReplyKeyboardManager::REPLYKEYBOARDBUTTON_IS_CONSTANT => true,
        ]);

        return [
            new SendMessageCommand(
                $update->getMessage()->getChat()->getId(),
                $this->render('index'),
                [
                    'parseMode' => $this->textFormat,
                ]
            ),
        ];
    }
}
