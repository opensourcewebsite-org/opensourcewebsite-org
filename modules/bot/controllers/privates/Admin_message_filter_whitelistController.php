<?php

namespace app\modules\bot\controllers\privates;

use Yii;
use \app\modules\bot\components\response\commands\SendMessageCommand;
use \app\modules\bot\components\response\commands\EditMessageTextCommand;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use app\modules\bot\components\Controller as Controller;
use app\modules\bot\models\Chat;
use app\modules\bot\models\Phrase;
use yii\data\Pagination;
use app\modules\bot\components\helpers\PaginationButtons;

/**
 * Class Admin_message_filter_whitelistController
 *
 * @package app\controllers\bot
 */
class Admin_message_filter_whitelistController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex($chatId = null, $page = 1)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat)) {
            return [];
        }

        $phraseQuery = $chat->getWhitelistPhrases();

        $pagination = new Pagination([
            'totalCount' => $phraseQuery->count(),
            'pageSize' => 9,
            'params' => [
                'page' => $page,
            ],
        ]);

        $pagination->pageSizeParam = false;
        $pagination->validatePage = true;

        $telegramUser = $this->getTelegramUser();
        $telegramUser->getState()->setName(null);
        $telegramUser->save();

        $chatTitle = $chat->title;
        $phrases = $phraseQuery->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $paginationButtons = PaginationButtons::build('/admin_message_filter_whitelist ' . $chatId . ' ', $pagination);
        $buttons = [];

        if ($phrases) {
            foreach ($phrases as $phrase) {
                $buttons[][] = ['callback_data' => '/admin_message_filter_phrase ' . $phrase->id, 'text' => $phrase->text];
            }

            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }
        }

        $buttons[] = [
            [
                'callback_data' => '/admin_message_filter ' . $chatId,
                'text' => '🔙',
            ],
            [
                'callback_data' => '/admin_message_filter_newphrase ' . Phrase::TYPE_WHITELIST . ' ' . $chatId,
                'text' => '➕',
            ],
        ];

        Yii::warning($buttons);

        if ($this->getUpdate()->getCallbackQuery()) {
            return [
                new EditMessageTextCommand(
                    $this->getTelegramChat()->chat_id,
                    $this->getUpdate()->getCallbackQuery()->getMessage()->getMessageId(),
                    $this->render('index', compact('chatTitle')),
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
                    $this->render('index', compact('chatTitle')),
                    [
                        'parseMode' => $this->textFormat,
                        'replyMarkup' => new InlineKeyboardMarkup($buttons),
                    ]
                ),
            ];
        }
    }
}
