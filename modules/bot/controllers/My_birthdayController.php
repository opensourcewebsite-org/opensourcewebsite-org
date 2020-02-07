<?php

namespace app\modules\bot\controllers;

use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use Yii;
use \app\modules\bot\components\response\SendMessageCommandSender;
use \app\modules\bot\components\response\AnswerCallbackQueryCommandSender;
use \app\modules\bot\components\response\commands\SendMessageCommand;
use \app\modules\bot\components\response\commands\AnswerCallbackQueryCommand;

/**
 * Class My_birthdayController
 *
 * @package app\modules\bot\controllers
 */
class My_birthdayController extends Controller
{
    /**
     * @return string
     */
    public function actionIndex()
    {
        $text = $this->render('index', [
            'birthday' => (new \DateTime($this->module->user->birthday))->format('m.d.Y')
        ]);

        return [
            new SendMessageCommandSender(
                new SendMessageCommand([
                    'chatId' => $this->getUpdate()->getMessage()->getChat()->getId(),
                    'parseMode' => 'html',
                    'text' => $this->prepareText($text),
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/change_birthday',
                                'text' => Yii::t('bot', 'Change Birthday'),
                            ]
                        ]
                    ]),
                ])
            ),
        ];
    }

    public function actionCreate()
    {
        $update = $this->getUpdate();
        $botClient = $this->getBotClient();
        $user = $this->getUser();

        $text = $update->getMessage()->getText();
        $user->birthday = $text;
        if ($success = $user->save())
        {
            $botClient->resetState();
            $botClient->save();
        }

        $text = $this->render('create', [
            'success' => $success,
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

    public function actionUpdate()
    {
        $update = $this->getUpdate();
        $botClient = $this->getBotClient();

        $botClient->setState([
            'state' => '/set_birthday',
        ]);
        $botClient->save();

        $text = $this->render('update');

        return [
            new SendMessageCommandSender(
                new SendMessageCommand([
                    'chatId' => $update->getCallbackQuery()->getMessage()->getChat()->getId(),
                    'parseMode' => 'html',
                    'text' => $this->prepareText($text),
                ])
            ),
            new AnswerCallbackQueryCommandSender(
                new AnswerCallbackQueryCommand([
                    'callbackQueryId' => $update->getCallbackQuery()->getId(),
                ])
            ),
        ];
    }
}
