<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use Yii;

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
        $this->getState()->clearInputRoute();

        $user = $this->getTelegramUser();
        $globalUser = $this->getGlobalUser();

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'telegramUser' => $user,
                    'user' => $globalUser,
                ]),
                [
                    [
                        [
                            'callback_data' => MyTimezoneController::createRoute(),
                            'text' => Yii::t('bot', 'Timezone'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => MyCurrencyController::createRoute(),
                            'text' => (!$globalUser->currency ? Emoji::WARNING . ' ' : '') . Yii::t('bot', 'Currency'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => MyLanguagesController::createRoute(),
                            'text' => (!$globalUser->languages ? Emoji::WARNING . ' ' : '') . Yii::t('bot', 'Languages'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => MyCitizenshipsController::createRoute(),
                            'text' => (!$globalUser->citizenships ? Emoji::WARNING . ' ' : '') . Yii::t('bot', 'Citizenships'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => MyBirthdayController::createRoute(),
                            'text' => (!$globalUser->birthday ? Emoji::WARNING . ' ' : '') . Yii::t('bot', 'Birthday'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => MyGenderController::createRoute(),
                            'text' => (!$globalUser->gender ? Emoji::WARNING . ' ' : '') . Yii::t('bot', 'Gender'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => MySexualityController::createRoute(),
                            'text' => (!$globalUser->sexuality ? Emoji::WARNING . ' ' : '') . Yii::t('bot', 'Sexuality'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => MyUsernameController::createRoute(),
                            'text' => (!$globalUser->username ? Emoji::WARNING . ' ' : '') . 'Username',
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
