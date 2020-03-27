<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\helpers\Emoji;
use Yii;
use app\modules\bot\components\response\commands\EditMessageTextCommand;
use app\modules\bot\components\response\commands\AnswerCallbackQueryCommand;
use app\modules\bot\components\response\commands\SendMessageCommand;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
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
    public function actionIndex($gender = null)
    {
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
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/my_profile',
                                'text' => Emoji::BACK,
                            ],
                            [
                                'callback_data' => '/my_gender__update',
                                'text' => Emoji::EDIT,
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

        return [
            new EditMessageTextCommand(
                $this->getTelegramChat()->chat_id,
                $update->getCallbackQuery()->getMessage()->getMessageId(),
                $text = $this->render('update', [
                    'gender' => $user->gender,
                ]),
                [
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/my_gender_male',
                                'text' => Yii::t('bot', 'Male'),
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/my_gender_female',
                                'text' => Yii::t('bot', 'Female'),
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/my_gender',
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
