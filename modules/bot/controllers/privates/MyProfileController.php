<?php

namespace app\modules\bot\controllers\privates;

use app\models\UserCitizenship;
use app\models\UserLanguage;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\response\ResponseBuilder;
use Yii;
use app\modules\bot\components\Controller;

/**
 * Class MyProfileController
 *
 * @package app\modules\bot\controllers
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
        $currency = $user->currency;

        $params = [
            'firstName' => $telegramUser->provider_user_first_name,
            'lastName' => $telegramUser->provider_user_last_name,
            'username' => $telegramUser->provider_user_name,
            'gender' => isset($user->gender) ? $user->gender->name : null,
            'sexuality' => isset($user->sexuality) ? $user->sexuality->name : null,
            'birthday' => $user->birthday,
            'currency' => isset($currency) ? "{$currency->name} ({$currency->code})" : null,
            'timezone' => $user->timezone->getUTCOffset(),
            'languages' => array_map(function (UserLanguage $userLanguage) {
                return $userLanguage->getDisplayName();
            }, $user->languages),
            'citizenships' => array_map(function(UserCitizenship $citizenship) {
                return $citizenship->country->name;
            }, $user->citizenships),
        ];

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('index', $params),
                [
                    [
                        [
                            'callback_data' => MyLocationController::createRoute(),
                            'text' => Yii::t('bot', 'Location'),
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
                            'text' => Yii::t('bot', 'Languages'),
                            'callback_data' => MyLanguagesController::createRoute(),
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
                            'callback_data' => MyBirthdayController::createRoute(),
                            'text' => Yii::t('bot', 'Birthday'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => MyCitizenshipController::createRoute(),
                            'text' => Yii::t('bot', 'Citizenship'),
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
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::MENU,
                        ],
                    ],
                ]
            )
            ->build();
    }
}
