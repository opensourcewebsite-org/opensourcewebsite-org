<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\models\Currency;
use app\models\Language;
use app\components\helpers\TimeHelper;
use app\modules\bot\components\helpers\ExternalLink;
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
            'firstName' => $telegramUser->provider_user_first_name,
            'lastName' => $telegramUser->provider_user_last_name,
            'username' => $telegramUser->provider_user_name,
            'gender' => isset($user->gender) ? $user->gender->name : null,
            'sexuality' => isset($user->sexuality) ? $user->sexuality->name : null,
            'birthday' => $user->birthday,
            'currency' => isset($user->currency) ? "{$user->currency->name} ({$user->currency->code})" : null,
            'timezone' => TimeHelper::getNameByOffset($user->timezone),
            'languages' => array_map(function ($userLanguage) {
                return $userLanguage->getLabel();
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
