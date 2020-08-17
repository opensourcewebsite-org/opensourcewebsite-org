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
 * @package app\modules\bot\controllers\privates
 */
class MyAccountController extends Controller
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
                [
                    'disablePreview' => true,
                ]
            )
            ->build();
    }
}
