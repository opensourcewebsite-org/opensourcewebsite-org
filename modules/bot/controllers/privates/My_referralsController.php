<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\response\SendMessageCommand;
use app\modules\bot\components\Controller as Controller;

/**
 * Class My_profileController
 *
 * @package app\modules\bot\controllers
 */
class My_referralsController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        $update = $this->getUpdate();
        $user = $this->getUser();

        $referralsCount = $user->getReferrals()->count();
        $userId = $this->getUser()->id;
        $websiteRefUrl = Yii::$app->urlManager->createAbsoluteUrl(["invite/$userId"]);
        $botName = $this->getBotName();
        $botRefUrl = "https://t.me/$botName?start=$userId";

        return [
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('index', [
                    'referralsCount' => $referralsCount,
                ]),
                [
                    'parseMode' => $this->textFormat,
                ]
            ),
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('invite-template', [
                    'websiteRefUrl' => $websiteRefUrl,
                    'botRefUrl' => $botRefUrl,
                ]),
                [
                    'parseMode' => $this->textFormat,
                ]
            ),
        ];
    }
}
