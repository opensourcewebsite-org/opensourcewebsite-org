<?php

namespace app\modules\bot\controllers;

use \app\modules\bot\components\response\SendMessageCommandSender;
use \app\modules\bot\components\response\commands\SendMessageCommand;

/**
 * Class My_ratingController
 *
 * @package app\modules\bot\controllers
 */
class My_ratingController extends Controller
{
    /**
     * @return string
     */
    public function actionIndex()
    {
        $update = $this->getUpdate();

        $params = [
            'active_rating' => 0,
            'overall_rating' => [0, 1000],
            'ranking' => [120, 120],
        ];

        $text = $this->render('index', $params);

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
