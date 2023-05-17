<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\ExternalLink;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\ChatSetting;
use app\modules\bot\models\User;
use Yii;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;

/**
 * Class ChannelController
 *
 * @package app\modules\bot\controllers\privates
 */
class ChannelController extends Controller
{
    /**
     * @param int $page
     * @return array
     */
    public function actionIndex($page = 1)
    {
        $this->getState()->clearInputRoute();

        $query = $this->getTelegramUser()->getActiveAdministratedChannels();

        $pagination = new Pagination([
            'totalCount' => $query->count(),
            'pageSize' => 9,
            'params' => [
                'page' => $page,
            ],
            'pageSizeParam' => false,
            'validatePage' => true,
        ]);

        $buttons = [];

        $chats = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        if ($chats) {
            foreach ($chats as $chat) {
                $buttons[][] = [
                    'callback_data' => self::createRoute('view', [
                        'chatId' => $chat->id,
                    ]),
                    'text' => $chat->title,
                ];
            }

            $paginationButtons = PaginationButtons::build($pagination, function ($page) {
                return self::createRoute('index', [
                    'page' => $page,
                ]);
            });

            if ($paginationButtons) {
                $buttons[] = $paginationButtons;
            }
        }

        $buttons[] = [
            [
                'callback_data' => TelegramController::createRoute(),
                'text' => Emoji::BACK,
            ],
            [
                'callback_data' => MenuController::createRoute(),
                'text' => Emoji::MENU,
            ],
        ];

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('index'),
                $buttons
            )
            ->build();
    }

    /**
     * @param int $chatId Chat->id
     * @return array
     */
    public function actionView($chatId = null)
    {
        $chat = Chat::findOne($chatId);

        if (!isset($chat) || !$chat->isChannel()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $chatMember = $chat->getChatMemberByUserId();

        if (!isset($chatMember) || !$chatMember->isActiveAdministrator()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $this->getState()->clearInputRoute();

        $administrators = $chat->getActiveAdministrators()->all();

        return $this->getResponseBuilder()
            ->editMessageTextOrSendMessage(
                $this->render('view', [
                    'chat' => $chat,
                    'administrators' => $administrators,
                ]),
                [
                    [
                        [
                            'callback_data' => ChannelAdministratorsController::createRoute('index', [
                                'id' => $chat->id,
                            ]),
                            'text' => Yii::t('bot', 'Administrators'),
                            'visible' => $chatMember->isCreator(),
                        ],
                    ],
                    [
                        [
                            'callback_data' => ChannelController::createRoute(),
                            'text' => Emoji::BACK,
                        ],
                        [
                            'callback_data' => MenuController::createRoute(),
                            'text' => Emoji::MENU,
                        ],
                        [
                            'callback_data' => ChannelRefreshController::createRoute('index', [
                                'id' => $chat->id,
                            ]),
                            'text' => Emoji::REFRESH,
                        ],
                        [
                            'url' => ExternalLink::getTelegramAccountLink($chat->getUsername()),
                            'text' => Yii::t('bot', 'Channel'),
                            'visible' => (bool)$chat->getUsername(),
                        ],
                    ],
                ],
                [
                    'disablePreview' => true,
                ]
            )
            ->build();
    }
}
