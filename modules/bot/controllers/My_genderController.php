<?php

namespace app\modules\bot\controllers;

use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use Yii;
use \app\modules\bot\components\response\EditMessageTextCommand;
use \app\modules\bot\components\response\AnswerCallbackQueryCommand;
use \app\modules\bot\components\response\SendMessageCommand;
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

    	return [
            new SendMessageCommand(
                $update->getMessage()->getChat()->getId(),
                $this->render('index', [
                    'gender' => $user->gender,
                ]),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/change_gender',
                                'text' => Yii::t('bot', 'Change Gender')
                            ],
                        ],
                    ]),
                ]
            ),
        ];
    }

    public function actionChange()
    {
        $update = $this->getUpdate();
        $user = $this->getUser();;

    	return [
            new EditMessageTextCommand(
                $update->getCallbackQuery()->getMessage()->getChat()->getId(),
                $update->getCallbackQuery()->getMessage()->getMessageId(),
                $text = $this->render('index', [
                    'gender' => $user->gender,
                ]),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => ($user->gender == User::FEMALE)
                                    ? '/set_gender_male'
                                    : '/set_gender_female',
                                'text' => Yii::t('bot', ($user->gender == User::FEMALE)
                                    ? 'Male'
                                    : 'Female'),
                            ],
                            [
                                'callback_data' => '/set_gender_back',
                                'text' => Yii::t('bot', 'Cancel'),
                            ],
                        ],
                    ]),
                ]
            ),
            new AnswerCallbackQueryCommand(
                $update->getCallbackQuery()->getId()
            ),
    	];
    }

    public function actionSetMale()
    {
        $update = $this->getUpdate();
        $user = $this->getUser();

        $user->gender = User::MALE;
        $user->save();

        return [
            new EditMessageTextCommand(
                $update->getCallbackQuery()->getMessage()->getChat()->getId(),
                $update->getCallbackQuery()->getMessage()->getMessageId(),
                $this->render('index', [
                    'gender' => $user->gender,
                ]),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/change_gender',
                                'text' => Yii::t('bot', 'Change Gender')
                            ],
                        ],
                    ]),
                ]
            ),
        ];
    }

    public function actionSetFemale()
    {
        $update = $this->getUpdate();
        $user = $this->getUser();

        $user->gender = User::FEMALE;
        $user->save();

        return [
            new EditMessageTextCommand(
                $update->getCallbackQuery()->getMessage()->getChat()->getId(),
                $update->getCallbackQuery()->getMessage()->getMessageId(),
                $this->render('index', [
                    'gender' => $user->gender,
                ]),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/change_gender',
                                'text' => Yii::t('bot', 'Change Gender')
                            ],
                        ],
                    ]),
                ]
            ),
        ];
    }

    public function actionBack()
    {
        $update = $this->getUpdate();
        $user = $this->getUser();

        return [
            new EditMessageTextCommand(
                $update->getCallbackQuery()->getMessage()->getChat()->getId(),
                $update->getCallbackQuery()->getMessage()->getMessageId(),
                $this->render('index', [
                    'gender' => $user->gender,
                ]),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/change_gender',
                                'text' => Yii::t('bot', 'Change Gender')
                            ],
                        ],
                    ]),
                ]
            ),
        ];
    }
}
