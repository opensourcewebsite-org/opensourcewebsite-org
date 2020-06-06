<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\helpers\Emoji;

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
        return $this->getResponseBuilder()
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
                            'callback_data' => AdsController::createRoute(),
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
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::MENU,
                        ],
                    ],
                ]
            )
            ->build();
    }
}
