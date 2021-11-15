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
                            'callback_data' => MyLocationController::createRoute(),
                            'text' => (!$user->userLocation ? Emoji::WARNING . ' ' : '') . Yii::t('bot', 'Location'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => MyTimezoneController::createRoute(),
                            'text' => Yii::t('bot', 'Timezone'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => MyCurrencyController::createRoute(),
                            'text' => (!$user->currency ? Emoji::WARNING . ' ' : '') . Yii::t('bot', 'Currency'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => MyLanguagesController::createRoute(),
                            'text' => (!$user->languages ? Emoji::WARNING . ' ' : '') . Yii::t('bot', 'Languages'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => MyCitizenshipsController::createRoute(),
                            'text' => (!$user->citizenships ? Emoji::WARNING . ' ' : '') . Yii::t('bot', 'Citizenships'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => MyBirthdayController::createRoute(),
                            'text' => (!$user->birthday ? Emoji::WARNING . ' ' : '') . Yii::t('bot', 'Birthday'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => MyGenderController::createRoute(),
                            'text' => (!$user->gender ? Emoji::WARNING . ' ' : '') . Yii::t('bot', 'Gender'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => MySexualityController::createRoute(),
                            'text' => (!$user->sexuality ? Emoji::WARNING . ' ' : '') . Yii::t('bot', 'Sexuality'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => MyEmailController::createRoute(),
                            'text' => (!$user->userEmail || !$user->userEmail->isConfirmed() ? Emoji::WARNING . ' ' : '') . Yii::t('bot', 'Email'),
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
