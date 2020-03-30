<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\response\ResponseBuilder;
use Yii;
use app\modules\bot\components\Controller;
use app\models\Currency;
use app\models\Language;
use app\components\helpers\TimeHelper;

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
        $interfaceLanguage = $telegramUser->language;

        $params = [
            'firstName' => $telegramUser->provider_user_first_name,
            'lastName' => $telegramUser->provider_user_last_name,
            'username' => $telegramUser->provider_user_name,
            'gender' => isset($user->gender) ? $user->gender->type : null,
            'birthday' => $user->birthday,
            'currency' => isset($currency) ? "{$currency->name} ({$currency->code})" : null,
            'interfaceLanguage' => isset($interfaceLanguage) ? "$interfaceLanguage->name (" . strtoupper($interfaceLanguage->code) . ')' : null,
            'timezone' => $timezones[$user->timezone],
            'languages' => array_map(function ($userLanguage) {
                return $userLanguage->getDisplayName();
            }, $user->languages),
            'citizenships' => array_map(function($citizenship) {
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
