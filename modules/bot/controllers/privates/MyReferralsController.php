<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
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
        $user = $this->getUser();

        $referralsCount = $user->getReferrals()->count();
        $userId = $this->getUser()->id;
        $websiteRefUrl = Yii::$app->urlManager->createAbsoluteUrl(["invite/$userId"]);
        $botName = $this->getBot()->name;
        $botRefUrl = "https://t.me/$botName?start=$userId";

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', [
                    'referralsCount' => $referralsCount,
                ])
            )
            ->sendMessage(
                $this->render('invite-template', [
                    'websiteRefUrl' => $websiteRefUrl,
                    'botRefUrl' => $botRefUrl,
                ]),
                [
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
