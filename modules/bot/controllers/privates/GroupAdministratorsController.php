<?php

namespace app\modules\bot\controllers\privates;

use app\modules\bot\components\Controller;
use app\modules\bot\components\helpers\Emoji;
use app\modules\bot\components\helpers\PaginationButtons;
use app\modules\bot\models\Chat;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\ChatSetting;
use Yii;
use yii\data\Pagination;

/**
 * Class GroupAdministratorsController
 *
 * @package app\modules\bot\controllers\privates
 */
class GroupAdministratorsController extends Controller
{
    /**
     * @param int $page
     * @param int|null $chatId
     * @return array
     */
    public function actionIndex($page = 1, $chatId = null)
    {
        $this->getState()->setName(null);

        if ($chatId) {
            $chat = Chat::findOne($chatId);

            if (!isset($chat) || !$chat->isGroup()) {
                return $this->getResponseBuilder()
                    ->answerCallbackQuery()
                    ->build();
            }

            $user = $this->getTelegramUser();

            $chatMember = ChatMember::findOne([
                'chat_id' => $chat->id,
                'user_id' => $user->id,
            ]);

            if (!isset($chatMember) || !$chatMember->isCreator()) {
                return $this->getResponseBuilder()
                    ->answerCallbackQuery()
                    ->build();
            }

            $query = $chat->getHumanAdministrators();

            $pagination = new Pagination([
                'totalCount' => $query->count(),
                'pageSize' => 9,
                'params' => [
                    'page' => $page,
                ],
                'pageSizeParam' => false,
                'validatePage' => true,
            ]);

            $paginationButtons = PaginationButtons::build($pagination, function ($page) use ($chat) {
                return self::createRoute('index', [
                    'chatId' => $chat->id,
                    'page' => $page,
                ]);
            });

            $buttons = [];

            $administrators = $query->offset($pagination->offset)
                ->limit($pagination->limit)
                ->all();

            if ($administrators) {
                foreach ($administrators as $administrator) {
                    $administratorChatMember = $chat->getChatMemberByUser($administrator);

                    $buttons[][] = [
                        'callback_data' => self::createRoute('set', [
                            'chatId' => $chatId,
                            'administratorId' => $administrator->id,
                        ]),
                        'text' => ($administratorChatMember->status == ChatMember::STATUS_CREATOR ? Emoji::CROWN : ($administratorChatMember->role == ChatMember::ROLE_ADMINISTRATOR ? Emoji::STATUS_ON : Emoji::STATUS_OFF)) . ' ' . ($administrator->provider_user_name ? '@' . $administrator->provider_user_name . ' - ' : '') . $administrator->getFullName(),
                    ];
                }

                if ($paginationButtons) {
                    $buttons[] = $paginationButtons;
                }
            }

            $buttons[] = [
                [
                    'callback_data' => GroupController::createRoute('view', [
                        'chatId' => $chatId,
                    ]),
                    'text' => Emoji::BACK,
                ],
                [
                    'callback_data' => MenuController::createRoute(),
                    'text' => Emoji::MENU,
                ],
            ];

            return $this->getResponseBuilder()
                ->editMessageTextOrSendMessage(
                    $this->render('index', [
                        'chat' => $chat,
                    ]),
                    $buttons
                )
                ->build();
        }
    }

    // TODO remove this action and join it to 'administrators' action to display the current page correctly
    public function actionSet($chatId = null, $administratorId = null)
    {
        $this->getState()->setName(null);

        $chat = Chat::findOne($chatId);

        if (!isset($chat) || !$chat->isGroup()) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $user = $this->getTelegramUser();

        $chatMember = ChatMember::findOne([
            'chat_id' => $chat->id,
            'user_id' => $user->id,
        ]);

        // creator cannot be deactivated
        if (!isset($chatMember) || !$chatMember->isCreator() || ($chatMember->getUserId() == $administratorId)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        $administratorChatMember = ChatMember::findOne([
            'chat_id' => $chat->id,
            'user_id' => $administratorId,
        ]);

        if (!isset($administratorChatMember)) {
            return $this->getResponseBuilder()
                ->answerCallbackQuery()
                ->build();
        }

        if ($administratorChatMember->isActiveAdministrator()) {
            $administratorChatMember->role = ChatMember::ROLE_MEMBER;
        } else {
            $administratorChatMember->role = ChatMember::ROLE_ADMINISTRATOR;
        }

        $administratorChatMember->save();

        return $this->runAction('index', [
             'chatId' => $chatId,
         ]);
    }
}
