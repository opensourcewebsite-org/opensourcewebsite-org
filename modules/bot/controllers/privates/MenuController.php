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
        $this->getState()->setName(null);

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'user' => $this->user,
                ]),
                [
                    [
                        [
                            'callback_data' => SAdController::createRoute(),
                            'text' => Yii::t('bot', 'Ads'),
                            'visible' => YII_ENV_DEV,
                        ],
                    ],
                    [
                        [
                            'callback_data' => SJobController::createRoute(),
                            'text' => Yii::t('bot', 'Jobs'),
                            'visible' => YII_ENV_DEV,
                        ],
                    ],
                    [
                        [
                            'callback_data' => TelegramController::createRoute(),
                            'text' => Yii::t('bot', 'Telegram Catalog'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => TelegramAdminController::createRoute(),
                            'text' => Yii::t('bot', 'Telegram Admin'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => ServicesController::createRoute(),
                            'text' => Emoji::DEVELOPMENT . ' ' . Yii::t('bot', 'Development'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => MyAccountController::createRoute(),
                            'text' => Yii::t('bot', 'Account'),
                        ],
                    ],
                    [
                        [
                            'url' => 'https://github.com/opensourcewebsite-org/opensourcewebsite-org/blob/master/DONATE.md',
                            'text' => Emoji::DONATE . ' ' . Yii::t('bot', 'Donate'),
                        ],
                        [
                            'url' => 'https://github.com/opensourcewebsite-org/opensourcewebsite-org/blob/master/CONTRIBUTING.md',
                            'text' => Emoji::CONTRIBUTE . ' ' . Yii::t('bot', 'Contribute'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => StartController::createRoute(),
                            'text' => Emoji::GREETING,
                        ],
                        [
                            'callback_data' => LanguageController::createRoute(),
                            'text' => Emoji::LANGUAGE . ' ' . strtoupper(Yii::$app->language),
                        ],
                    ],
                ]
            )
            ->build();
    }
}
