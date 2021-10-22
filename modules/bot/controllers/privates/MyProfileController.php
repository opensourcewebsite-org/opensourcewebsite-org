<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\models\Currency;
use app\models\Language;
use app\modules\bot\components\helpers\Emoji;

/**
 * Class MyProfileController
 *
 * @package app\modules\bot\controllers\privates
 */
class MyProfileController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        $telegramUser = $this->getTelegramUser();
        $user = $this->getUser();

        $params = [
            'telegramUser' => $telegramUser,
            'user' => $user,
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', $params),
                [
                    [
                        [
                            'callback_data' => MyTimezoneController::createRoute(),
                            'text' => Yii::t('bot', 'Timezone'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => MyBirthdayController::createRoute(),
                            'text' => Yii::t('bot', 'Birthday'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => MyGenderController::createRoute(),
                            'text' => Yii::t('bot', 'Gender'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => MySexualityController::createRoute(),
                            'text' => Yii::t('bot', 'Sexuality'),
                        ],
                    ],
                    [
                        [
                            'text' => Yii::t('bot', 'Languages'),
                            'callback_data' => MyLanguagesController::createRoute(),
                        ],
                    ],
                    [
                        [
                            'callback_data' => MyCitizenshipsController::createRoute(),
                            'text' => Yii::t('bot', 'Citizenships'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => MyCurrencyController::createRoute(),
                            'text' => Yii::t('bot', 'Currency'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => MyEmailController::createRoute(),
                            'text' => Yii::t('bot', 'Email'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => MyAccountController::createRoute(),
                            'text' => Emoji::BACK,
                        ],
                        [
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::MENU,
                        ],
                    ],
                ],
                [
                    'disablePreview' => true,
                ]
            )
            ->build();
    }
}
