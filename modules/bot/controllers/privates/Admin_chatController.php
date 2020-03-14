<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use \app\modules\bot\components\response\EditMessageTextCommand;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use app\modules\bot\components\Controller as Controller;
use app\modules\bot\models\Chat;

/**
 * Class Admin_chatController
 *
 * @package app\controllers\bot
 */
class Admin_chatController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex($chatId)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat)) {
            return [];
        }

        $chatTitle = $chat->title;

        return [
            new EditMessageTextCommand(
                $this->getTelegramChat()->chat_id,
                $this->getUpdate()->getCallbackQuery()->getMessage()->getMessageId(),
                $this->render('index', compact('chatTitle')),
                [
                    'parseMode' => $this->textFormat,
                    'replyMarkup' => new InlineKeyboardMarkup([
                        [
                            [
                                'callback_data' => '/admin_message_filter ' . $chatId,
                                'text' => Yii::t('bot', 'Message Filter'),
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/admin_join_hider ' . $chatId,
                                'text' => Yii::t('bot', 'Join Hider'),
                            ],
                        ],
                        [
                            [
                                'callback_data' => '/admin',
                                'text' => '🔙',
                            ],
                        ],
                    ]),
                ]
            ),
        ];
    }
}
