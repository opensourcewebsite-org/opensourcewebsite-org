<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use \app\modules\bot\components\response\EditMessageTextCommand;
use \app\modules\bot\components\response\AnswerCallbackQueryCommand;
use \app\modules\bot\components\response\SendMessageCommand;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use \app\models\User;
use app\modules\bot\components\Controller as Controller;

/**
 * Class MyGenderController
 *
 * @package app\modules\bot\controllers
 */
class MyGenderController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex($gender = null)
    {
        $update = $this->getUpdate();
        $user = $this->getUser();

        if ($gender) {
            if ($gender == 'male') {
                $user->gender = User::MALE;
            } elseif ($gender == 'female') {
                $user->gender = User::FEMALE;
            }
            $user->save();
        }

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
                                'callback_data' => MyProfileController::createRoute(),
                                'text' => '🔙',
                            ],
                            [
                                'callback_data' => self::createRoute('update'),
                                'text' => '✏️',
                            ],
                        ],
                    ]),
                ]
            ),
        ];
    }

    public function actionUpdate()
    {
        $update = $this->getUpdate();
        $user = $this->getUser();

        if ($this->getUpdate()->getCallbackQuery()) {
            return [
                new EditMessageTextCommand(
                    $this->getTelegramChat()->chat_id,
                    $update->getCallbackQuery()->getMessage()->getMessageId(),
                    $text = $this->render('update', [
                        'gender' => $user->gender,
                    ]),
                    [
                        'parseMode' => $this->textFormat,
                        'replyMarkup' => new InlineKeyboardMarkup([
                            [
                                [
                                    'callback_data' => self::createRoute('index', [
                                        'gender' => 'male',
                                    ]),
                                    'text' => Yii::t('bot', 'Male'),
                                ],
                            ],
                            [
                                [
                                    'callback_data' => self::createRoute('index', [
                                        'gender' => 'female',
                                    ]),
                                    'text' => Yii::t('bot', 'Female'),
                                ],
                            ],
                            [
                                [
                                    'callback_data' => self::createRoute(),
                                    'text' => '🔙',
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
    }
}
