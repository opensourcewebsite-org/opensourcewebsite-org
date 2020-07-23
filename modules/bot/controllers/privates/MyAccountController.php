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
 * Class MyAccountController
 *
 * @package app\modules\bot\controllers
 */
class MyAccountController extends Controller
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
                            'callback_data' => MyRatingController::createRoute(),
                            'text' => Yii::t('bot', 'Rating'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => MyReferralsController::createRoute(),
                            'text' => Yii::t('bot', 'Referrals')
                        ],
                    ],
                    [
                        [
                            'callback_data' => MyLocationController::createRoute(),
                            'text' => Yii::t('bot', 'Location'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => MyProfileController::createRoute(),
                            'text' => Yii::t('bot', 'Profile'),
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
