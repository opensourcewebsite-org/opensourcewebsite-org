<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use \app\modules\bot\components\response\SendMessageCommand;
use \app\modules\bot\components\response\EditMessageTextCommand;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use app\modules\bot\components\Controller as Controller;

/**
 * Class AdminController
 *
 * @package app\controllers\bot
 */
class AdminController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        $chats = $this->getTelegramUser()->getAdministratedChats()->all();

        $buttons = [];
        $currentRow = [];

        foreach ($chats as $chat) {
            $currentRow[] = [
                'callback_data' => '/admin_chat ' . $chat->id,
                'text' => $chat->title,
            ];

            if (count($currentRow) == 2) {
                $buttons[] = $currentRow;
                $currentRow = [];
            }
        }

        if (!empty($currentRow)) {
            $buttons[] = $currentRow;
            $currentRow = [];
        }

        $buttons[] = [
            [
                'callback_data' => '/menu',
                'text' => '🔙',
            ],
        ];

        if ($this->getUpdate()->getCallbackQuery()) {
            return [
                new EditMessageTextCommand(
                    $this->getTelegramChat()->chat_id,
                    $this->getUpdate()->getCallbackQuery()->getMessage()->getMessageId(),
                    $this->render('index'),
                    [
                        'parseMode' => $this->textFormat,
                        'replyMarkup' => new InlineKeyboardMarkup($buttons),
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
                        'replyMarkup' => new InlineKeyboardMarkup($buttons),
                    ]
                ),
            ];
        }
    }
}
