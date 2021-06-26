<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;

/**
 * Class ServicesController
 *
 * @package app\modules\bot\controllers\privates
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
                            'text' => Yii::t('bot', 'Currency Exchange'),
                            'visible' => YII_ENV_DEV,
                        ],
                    ],
                    [
                        [
                            'callback_data' => SDaController::createRoute(),
                            'text' => Yii::t('bot', 'Dating'),
                            'visible' => YII_ENV_DEV,
                        ],
                    ],
                    [
                        [
                            'callback_data' => SReController::createRoute(),
                            'text' => Yii::t('bot', 'Real Estates'),
                            'visible' => YII_ENV_DEV,
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
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::MENU,
                        ],
                    ],
                ]
            )
            ->build();
    }
}
