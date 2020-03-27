<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\response\commands\SendLocationCommand;
use app\modules\bot\components\response\commands\SendMessageCommand;
use app\modules\bot\components\Controller;
use \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

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
                    $this->render('header')
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
                        'replyMarkup' => new InlineKeyboardMarkup([
                            [
                                [
                                    'callback_data' => MyProfileController::createRoute(),
                                    'text' => Emoji::BACK,
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
                        'replyMarkup' => new InlineKeyboardMarkup([
                            [
                                [
                                    'callback_data' => MyProfileController::createRoute(),
                                    'text' => Emoji::BACK,
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
