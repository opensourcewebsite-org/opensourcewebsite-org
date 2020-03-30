<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use \app\modules\bot\components\response\SendLocationCommand;
use \app\modules\bot\components\response\SendMessageCommand;
use \app\modules\bot\components\ReplyKeyboardManager;
use \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use app\modules\bot\components\Controller as Controller;

/**
 * Class MyLocationController
 *
 * @package app\modules\bot\controllers
 */
class MyLocationController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        $telegramUser = $this->getTelegramUser();

        if (isset($telegramUser->location_lon) && isset($telegramUser->location_lat)) {
            return [
                new SendMessageCommand(
                    $this->getTelegramChat()->chat_id,
                    $this->render('header'),
                    [
                        'parseMode' => $this->textFormat,
                    ]
                ),
                new SendLocationCommand(
                    $this->getTelegramChat()->chat_id,
                    $telegramUser->location_lat,
                    $telegramUser->location_lon
                ),
                new SendMessageCommand(
                    $this->getTelegramChat()->chat_id,
                    $this->render('footer'),
                    [
                        'parseMode' => $this->textFormat,
                        'replyMarkup' => new InlineKeyboardMarkup([
                            [
                                [
                                    'callback_data' => MyProfileController::createRoute(),
                                    'text' => '🔙',
                                ],
                            ],
                        ]),
                    ]
                ),
            ];
        } else {
            return [
                new SendMessageCommand(
                    $this->getTelegramChat()->chat_id,
                    $this->render('index'),
                    [
                        'parseMode' => $this->textFormat,
                        'replyMarkup' => new InlineKeyboardMarkup([
                            [
                                [
                                    'callback_data' => MyProfileController::createRoute(),
                                    'text' => '🔙',
                                ],
                            ],
                        ]),
                    ]
                ),
            ];
        }
    }

    public function actionUpdate()
    {
        $telegramUser = $this->getTelegramUser();
        $update = $this->getUpdate();

        if ($update->getMessage() && ($location = $update->getMessage()->getLocation())) {
            $telegramUser->setAttributes([
                'location_lon' => $location->getLongitude(),
                'location_lat' => $location->getLatitude(),
                'location_at' => time(),
            ]);
            $telegramUser->save();
        }

        return $this->actionIndex();
    }
}
