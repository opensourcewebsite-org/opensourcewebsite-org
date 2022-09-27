<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;
use Yii;

/**
 * Class MyReferralsController
 *
 * @package app\modules\bot\controllers\privates
 */
class MyReferralsController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        $globalUser = $this->getGlobalUser();

        $referralsCount = $globalUser->getReferrals()->count();

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'referralsCount' => $referralsCount,
                ]),
                [
                    [
                        [
                            'callback_data' => self::createRoute('referral-message'),
                            'text' => Yii::t('bot', 'Referral message'),
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
            ->send();
    }

    /**
     * @return array
     */
    public function actionReferralMessage()
    {
        $globalUser = $this->getGlobalUser();
        $user = $this->getTelegramUser();

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('referral-message', [
                    'globalUser' => $globalUser,
                    'user' => $user,
                ]),
                [
                    [
                        [
                            'callback_data' => MyReferralsController::createRoute(),
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
            ->send();
    }
}
