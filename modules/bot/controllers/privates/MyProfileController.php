<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\helpers\Emoji;

use Yii;
use app\modules\bot\components\Controller;
use app\models\Currency;
use app\models\Language;
use app\components\helpers\TimeHelper;
use app\modules\bot\components\helpers\ExternalLink;

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
        $timezones = TimeHelper::timezonesList();

        $currency = $user->currency;

        $params = [
            'firstName' => $telegramUser->provider_user_first_name,
            'lastName' => $telegramUser->provider_user_last_name,
            'username' => $telegramUser->provider_user_name,
            'gender' => isset($user->gender) ? $user->gender->name : null,
            'sexuality' => isset($user->sexuality) ? $user->sexuality->name : null,
            'birthday' => $user->birthday,
            'currency' => isset($currency) ? "{$currency->name} ({$currency->code})" : null,
            'timezone' => $timezones[$user->timezone],
            'languages' => array_map(function ($userLanguage) {
                return $userLanguage->getDisplayName();
            }, $user->languages),
            'citizenships' => array_map(function ($citizenship) {
                return $citizenship->country->name;
            }, $user->citizenships),
            'location_lat' => $telegramUser->location_lat,
            'location_lon' => $telegramUser->location_lon,
            'locationLink' => ExternalLink::getOSMLink($telegramUser->location_lat, $telegramUser->location_lon),
        ];

        return $this->getResponseBuilder()
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
                            'text' => Yii::t('bot', 'Languages'),
                            'callback_data' => MyLanguagesController::createRoute(),
                        ],
                    ],
                    [
                        [
                            'callback_data' => MyCitizenshipController::createRoute(),
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
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::MENU,
                        ],
                    ],
                ],
                true
            )
            ->build();
    }
}
