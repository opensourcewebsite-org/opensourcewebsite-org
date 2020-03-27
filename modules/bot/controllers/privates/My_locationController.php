<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\helpers\Emoji;
use Yii;
use app\modules\bot\components\response\commands\SendLocationCommand;
use app\modules\bot\components\response\commands\SendMessageCommand;
use \app\modules\bot\components\helpers\ReplyKeyboardManager;
use app\modules\bot\components\Controller;
use \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

/**
 * Class My_locationController
 *
 * @package app\modules\bot\controllers
 */
class My_locationController extends Controller
{
    public function actionIndex()
    {
        $telegramUser = $this->getTelegramUser();
        $update = $this->getUpdate();

        ReplyKeyboardManager::getInstance()->addKeyboardButton(0, [
            'text' => $this->render('send-location'),
            'request_location' => true,
        ]);

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
                                    'callback_data' => '/my_profile',
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
                                    'callback_data' => '/my_profile',
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
