<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\Controller;
use app\modules\bot\components\response\ResponseBuilder;
use app\modules\bot\components\helpers\PaginationButtons;

/**
 * Class AdminController
 *
 * @package app\controllers\bot
 */
class AdminController extends Controller
{
    /**
     * @param int $page
     * @return array
     */
    public function actionIndex($page = 1)
    {
        $chatButtons = PaginationButtons::buildFromQuery(
            $this->getTelegramUser()->getAdministratedChats(),
            function ($page) {
                return self::createRoute('index', [
                    'page' => $page,
                ]);
            },
            function (\app\modules\bot\models\Chat $chat) {
                return [
                    'callback_data' => AdminChatController::createRoute('index', [
                        'chatId' => $chat->id,
                    ]),
                    'text' => $chat->title,
                ];
            },
            $page
        );

        return ResponseBuilder::fromUpdate($this->getUpdate())
            ->editMessageTextOrSendMessage(
                $this->render('index'),
                array_merge($chatButtons, [
                    [
                        [
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::MENU,
                        ],
                    ],
                ])
            )
            ->build();
    }
}
