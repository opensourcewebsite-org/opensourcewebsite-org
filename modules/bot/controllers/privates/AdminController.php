<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\Controller;
use app\modules\bot\components\response\ResponseBuilder;
use yii\data\Pagination;
use app\modules\bot\components\helpers\PaginationButtons;

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

        $paginationButtons = PaginationButtons::build($pagination, function ($page) {
            return self::createRoute('index', [
                'page' => $page,
            ]);
        });
        $buttons = [];

        if ($chats) {
            foreach ($chats as $chat) {
                $buttons[][] = [
                    'callback_data' => AdminChatController::createRoute('index', [
                        'chatId' => $chat->id,
                    ]),
                    'text' => $chat->title,
                ];
            }

            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }
        }

        $buttons[][] = [
            'callback_data' => MenuController::createRoute(),
            'text' => Emoji::MENU,
        ];

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('index'),
                $buttons
            )
            ->build();
    }
}
