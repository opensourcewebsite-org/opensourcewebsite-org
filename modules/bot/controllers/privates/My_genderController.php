<?php

namespace app\modules\bot\controllers\privates;

use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use Yii;
use \app\modules\bot\components\response\EditMessageTextCommand;
use \app\modules\bot\components\response\AnswerCallbackQueryCommand;
use \app\modules\bot\components\response\SendMessageCommand;
use \app\models\User;
use app\modules\bot\components\Controller;

/**
 * Class My_genderController
 *
 * @package app\modules\bot\controllers
 */
class My_genderController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        $update = $this->getUpdate();
        $user = $this->getUser();

        return [
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
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
        $user = $this->getUser();

        return [
            new EditMessageTextCommand(
                $this->getTelegramChat()->chat_id,
                $update->getCallbackQuery()->getMessage()->getMessageId(),
                $text = $this->render('index', [
                    'gender' => $user->gender,
                ]),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
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
                $this->getTelegramChat()->chat_id,
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
                $this->getTelegramChat()->chat_id,
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
