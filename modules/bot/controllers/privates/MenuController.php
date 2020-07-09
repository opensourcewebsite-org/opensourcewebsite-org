<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;

/**
 * Class MenuController
 *
 * @package app\controllers\bot
 */
class MenuController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index'),
                [
                    [
                        [
                            'callback_data' => MyProfileController::createRoute(),
                            'text' => Yii::t('bot', 'Profile'),
                        ],
                    ],
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
                            'callback_data' => ServicesController::createRoute(),
                            'text' => Yii::t('bot', 'Services')
                        ],
                    ],
                    [
                        [
                            'callback_data' => AdminController::createRoute(),
                            'text' => Yii::t('bot', 'Groups')
                        ],
                    ],
                    [
                        [
                            'callback_data' => HelpController::createRoute(),
                            'text' => Yii::t('bot', 'Commands')
                        ],
                    ],
                    [
                        [
                            'url' => 'https://github.com/opensourcewebsite-org/opensourcewebsite-org/blob/master/DONATE.md',
                            'text' => '👼 ' . Yii::t('bot', 'Donate'),
                        ],
                        [
                            'url' => 'https://github.com/opensourcewebsite-org/opensourcewebsite-org/blob/master/CONTRIBUTING.md',
                            'text' => '👨‍🚀 ' . Yii::t('bot', 'Contribute'),
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
