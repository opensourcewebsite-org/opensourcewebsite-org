<?php

namespace app\modules\bot\controllers;

use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use Yii;
use \app\modules\bot\components\response\SendMessageCommandSender;
use \app\modules\bot\components\response\EditMessageTextCommandSender;
use \app\modules\bot\components\response\AnswerCallbackQueryCommandSender;
use \app\modules\bot\components\response\commands\EditMessageTextCommand;
use \app\modules\bot\components\response\commands\AnswerCallbackQueryCommand;
use \app\modules\bot\components\response\commands\SendMessageCommand;
use \app\models\User;

/**
 * Class My_genderController
 *
 * @package app\modules\bot\controllers
 */
class My_genderController extends Controller
{
    /**
     * @return string
     */
    public function actionIndex()
    {
        $update = $this->getUpdate();
        $user = $this->getUser();

        $text = $this->render('index', [
            'gender' => $user->gender,
        ]);

    	return [
            new SendMessageCommandSender(
                new SendMessageCommand([
                    'chatId' => $update->getMessage()->getChat()->getId(),
                    'parseMode' => 'html',
                    'text' => $this->prepareText($text),
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/change_gender',
                                'text' => Yii::t('bot', 'Change Gender')
                            ],
                        ],
                    ]),
                ])
            ),
        ];
    }

    public function actionChange()
    {
        $update = $this->getUpdate();
        $user = $this->getUser();

        $text = $this->render('index', [
            'gender' => $user->gender,
        ]);

    	return [
            new EditMessageTextCommandSender(
                new EditMessageTextCommand([
                    'chatId' => $update->getCallbackQuery()->getMessage()->getChat()->getId(),
                    'messageId' => $update->getCallbackQuery()->getMessage()->getMessageId(),
                    'parseMode' => 'html',
                    'text' => $this->prepareText($text),
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/set_gender_back',
                                'text' => Yii::t('bot', 'Back'),
                            ],
                            [
                                'callback_data' => '/set_gender_male',
                                'text' => Yii::t('bot', 'Male'),
                            ],
                            [
                                'callback_data' => '/set_gender_female',
                                'text' => Yii::t('bot', 'Female'),
                            ],
                        ],
                    ]),
                ])
            ),
            new AnswerCallbackQueryCommandSender(
                new AnswerCallbackQueryCommand([
                    'callbackQueryId' => $update->getCallbackQuery()->getId(),
                ])
            ),
    	];
    }

    public function actionSetMale()
    {
        $update = $this->getUpdate();
        $user = $this->getUser();

        $user->gender = User::MALE;
        $user->save();

        $text = $this->render('index', [
            'gender' => $user->gender,
        ]);

        return [
            new EditMessageTextCommandSender(
                new EditMessageTextCommand([
                    'chatId' => $update->getCallbackQuery()->getMessage()->getChat()->getId(),
                    'messageId' => $update->getCallbackQuery()->getMessage()->getMessageId(),
                    'parseMode' => 'html',
                    'text' => $this->prepareText($text),
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/change_gender',
                                'text' => Yii::t('bot', 'Change Gender')
                            ],
                        ],
                    ]),
                ])
            ),
        ];
    }

    public function actionSetFemale()
    {
        $update = $this->getUpdate();
        $user = $this->getUser();

        $user->gender = User::FEMALE;
        $user->save();

        $text = $this->render('index', [
            'gender' => $user->gender,
        ]);

        return [
            new EditMessageTextCommandSender(
                new EditMessageTextCommand([
                    'chatId' => $update->getCallbackQuery()->getMessage()->getChat()->getId(),
                    'messageId' => $update->getCallbackQuery()->getMessage()->getMessageId(),
                    'parseMode' => 'html',
                    'text' => $this->prepareText($text),
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/change_gender',
                                'text' => Yii::t('bot', 'Change Gender')
                            ],
                        ],
                    ]),
                ])
            ),
        ];
    }

    public function actionBack()
    {
        $update = $this->getUpdate();
        $user = $this->getUser();

        $text = $this->render('index', [
            'gender' => $user->gender,
        ]);
        
        return [
            new EditMessageTextCommandSender(
                new EditMessageTextCommand([
                    'chatId' => $update->getCallbackQuery()->getMessage()->getChat()->getId(),
                    'messageId' => $update->getCallbackQuery()->getMessage()->getMessageId(),
                    'parseMode' => 'html',
                    'text' => $this->prepareText($text),
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/change_gender',
                                'text' => Yii::t('bot', 'Change Gender')
                            ],
                        ],
                    ]),
                ])
            ),
        ];
    }
}
