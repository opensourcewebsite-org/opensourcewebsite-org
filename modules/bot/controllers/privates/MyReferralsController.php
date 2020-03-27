<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\helpers\Emoji;
use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\components\response\commands\SendMessageCommand;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

/**
 * Class MyReferralsController
 *
 * @package app\modules\bot\controllers
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
        $botName = $this->getBotName();
        $botRefUrl = "https://t.me/$botName?start=$userId";

        return [
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('index', [
                    'referralsCount' => $referralsCount,
                ]),
                [
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => MenuController::createRoute(),
                                'text' => Emoji::MENU,
                            ],
                        ],
                    ]),
                ]
            ),
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('invite-template', [
                    'websiteRefUrl' => $websiteRefUrl,
                    'botRefUrl' => $botRefUrl,
                ]),
                [
                    'disablePreview' => true,
                ]
            ),
        ];
    }
}
