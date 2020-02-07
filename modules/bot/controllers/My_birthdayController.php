<?php

namespace app\modules\bot\controllers;

use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use Yii;

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

        return [
            [
                'type' => 'message',
                'text' => $this->render('index',
                    [
                        'birthday' => (new \DateTime($this->module->user->birthday))->format('m.d.Y')
                    ]),
                'replyMarkup' => new InlineKeyboardMarkup(
                    [
                        [
                            [
                                'callback_data' => '/change_birthday',
                                'text' => Yii::t('bot', 'Change Birthday'),
                            ]
                        ]
                    ]),
            ]
        ];
    }

    public function actionCreate()
    {
        $text = $this->module->update->getMessage()->getText();
        $this->module->user->birthday = $text;
        if ($success = $this->module->user->save())
        {
            $this->module->botClient->setState();
            $this->module->botClient->save();
        }

        return [
            [
                'type' => 'message',
                'text' => $this->render('create',
                    [
                        'success' => $success,
                    ]),
            ]
        ];
    }

    public function actionUpdate()
    {
        $this->module->botClient->setState([
            'state' => '/set_birthday',
        ]);
        $this->module->botClient->save();

        return [
            [
                'type' => 'message',
                'text' => $this->render('update'),
            ],
            [
                'type' => 'callback'
            ]
        ];
    }
}
