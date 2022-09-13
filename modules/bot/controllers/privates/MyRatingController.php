<?php

namespace app\modules\bot\controllers\privates;

use app\components\Converter;
use app\models\Rating;
use app\models\User;
use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use Yii;

/**
 * Class MyRatingController
 *
 * @package app\modules\bot\controllers\privates
 */
class MyRatingController extends Controller
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
                            'callback_data' => MyAccountController::createRoute(),
                            'text' => Emoji::BACK,
                        ],
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
