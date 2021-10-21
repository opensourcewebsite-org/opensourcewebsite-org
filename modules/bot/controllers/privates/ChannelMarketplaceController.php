<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use app\modules\bot\components\Controller;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\components\helpers\Emoji;

/**
 * Class ChannelMarketplaceController
 *
 * @package app\modules\bot\controllers\privates
 */
class ChannelMarketplaceController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex($chatId = null)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat)) {
            return [];
        }

        $statusOn = ($chat->marketplace_status == ChatSetting::STATUS_ON);

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index', compact('chat')),
                [
                    [
                        [
                            'callback_data' => self::createRoute('set-status', [
                                'chatId' => $chatId,
                            ]),
                            'text' => $statusOn ? Emoji::STATUS_ON . ' ON' : Emoji::STATUS_OFF . ' OFF',
                        ],
                    ],
                    [
                        [
                            'callback_data' => ChannelController::createRoute('view', [
                                'chatId' => $chatId,
                            ]),
                            'text' => Emoji::BACK,
                        ],
                        [
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::MENU,
                        ],
                    ]
                ],
                [
                    'disablePreview' => true,
                ]
            )
            ->build();
    }

    public function actionSetStatus($chatId = null)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat)) {
            return [];
        }

        if ($chat->marketplace_status == ChatSetting::STATUS_ON) {
            $chat->marketplace_status = ChatSetting::STATUS_OFF;
        } else {
            $chat->marketplace_status = ChatSetting::STATUS_ON;
        }

        return $this->actionIndex($chatId);
    }
}
