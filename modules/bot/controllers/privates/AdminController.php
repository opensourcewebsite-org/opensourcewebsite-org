<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\Emoji;
use Yii;
use app\modules\bot\components\response\SendMessageCommand;
use app\modules\bot\components\Controller;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use app\modules\bot\components\response\EditMessageTextCommand;
use yii\data\Pagination;
use app\modules\bot\helpers\PaginationButtons;

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
    public function actionIndex($page = 1)
    {
        $chatQuery = $this->getTelegramUser()->getAdministratedChats();

        $buttons = [];
        $currentRow = [];

        $pagination = new Pagination([
            'totalCount' => $chatQuery->count(),
            'pageSize' => 9,
            'params' => [
                'page' => $page,
            ],
        ]);

        $pagination->pageSizeParam = false;
        $pagination->validatePage = true;

        $chats = $chatQuery->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $paginationButtons = PaginationButtons::build('/admin_', $pagination);
        $buttons = [];

        if ($chats) {
            foreach ($chats as $chat) {
                $buttons[][] = ['callback_data' => '/admin_chat ' . $chat->id, 'text' => $chat->title];
            }

            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }
        }

        $buttons[][] = [
            'callback_data' => '/menu',
            'text' => Emoji::BACK
        ];

        Yii::warning($buttons);

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
