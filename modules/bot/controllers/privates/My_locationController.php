<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use \app\modules\bot\components\response\SendLocationCommand;
use \app\modules\bot\components\response\SendMessageCommand;
use \app\modules\bot\components\ReplyKeyboardManager;
use \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use app\modules\bot\components\Controller as Controller;

/**
 * Class My_locationController
 *
 * @package app\modules\bot\controllers
 */
class My_locationController extends Controller
{
    /**
     * @return string
     */
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
                                    'callback_data' => '/my_profile',
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
                                    'callback_data' => '/my_profile',
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
