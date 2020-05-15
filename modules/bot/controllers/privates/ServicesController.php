<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\response\ResponseBuilder;
use Yii;
use app\modules\bot\components\Controller;

/**
 * Class ServicesController
 *
 * @package app\controllers\bot
 */
class ServicesController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('index'),
                [
                    [
                        [
                            'callback_data' => SCeController::createRoute(),
                            'text' => '🏗 ' . Yii::t('bot', 'Currency Exchange'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => SJobController::createRoute(),
                            'text' => '🏗 ' . Yii::t('bot', 'Jobs'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => SAdController::createRoute(),
                            'text' => '🏗 ' . Yii::t('bot', 'Ads'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => SDaController::createRoute(),
                            'text' => '🏗 ' . Yii::t('bot', 'Dating'),
                        ],
                    ],
                    [
                        [
                            'callback_data' => SReController::createRoute(),
                            'text' => '🏗 ' . Yii::t('bot', 'Real Estates'),
                        ],
                    ],
                    [
                        [
                            'url' => 'https://github.com/opensourcewebsite-org/opensourcewebsite-org/blob/master/DONATE.md',
                            'text' => '👼 ' . Yii::t('bot', 'Donate'),
                        ],
                        [
                            'url' => 'https://github.com/opensourcewebsite-org/opensourcewebsite-org/blob/master/CONTRIBUTING.md',
                            'text' => '👨‍🚀 ' . Yii::t('common', 'Contribution'),
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
