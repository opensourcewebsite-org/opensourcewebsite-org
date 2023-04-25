<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use Yii;

/**
 * Class MenuController
 *
 * @package app\modules\bot\controllers\privates
 */
class MenuController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        $this->getState()->clearInputRoute();

        $globalUser = $this->getUser();
        $user = $this->getTelegramUser();

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'user' => $globalUser,
                ]),
                [
                    [
                        [
                            'callback_data' => MyLocationController::createRoute(),
                            'text' => (!$globalUser->userLocation ? Emoji::WARNING . ' ' : '') . Yii::t('bot', 'Location'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => WalletController::createRoute(),
                            'text' => Yii::t('bot', 'Money'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => ContactController::createRoute(),
                            'text' => Yii::t('bot', 'Contacts'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => CaController::createRoute(),
                            'text' => Yii::t('bot', 'Cash Exchange'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => CeController::createRoute(),
                            'text' => Yii::t('bot', 'Currency Exchange'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => AdController::createRoute(),
                            'text' => Yii::t('bot', 'Ads'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => JoController::createRoute(),
                            'text' => Yii::t('bot', 'Jobs'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => TelegramController::createRoute(),
                            'text' => Yii::t('bot', 'Telegram'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => StartController::createRoute(),
                            'text' => Emoji::GREETING,
                        ],
                        [
                            'callback_data' => MyAccountController::createRoute(),
                            'text' => Yii::t('bot', 'Account'),
                        ],
                        [
                            'callback_data' => ServicesController::createRoute(),
                            'text' => Emoji::SOON,
                        ],
                        [
                            'callback_data' => LanguageController::createRoute(),
                            'text' => Emoji::LANGUAGE . ' ' . strtoupper(Yii::$app->language),
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
