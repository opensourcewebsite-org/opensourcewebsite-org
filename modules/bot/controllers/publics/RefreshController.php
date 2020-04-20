<?php

namespace app\modules\bot\controllers\publics;

use app\modules\bot\components\response\commands\SendMessageCommand;
use app\modules\bot\components\Controller as Controller;
use app\modules\bot\components\response\ResponseBuilder;
use app\modules\bot\models\ChatMember;
use app\modules\bot\models\User;

/**
 * Class FilterChatController
 *
 * @package app\controllers\bot
 */
class RefreshController extends Controller
{
    /**
     * @return array
     */
    public function actionIndex()
    {
        $chat = $this->getTelegramChat();
        $administratorUsers = [];
        $currentUser = $this->getTelegramUser();
        $currentUserIsAdmin = false;
        $currentAdministrators = $chat->getAdministrators()->all();

        $telegramAdministrators = $this->getBotApi()->getChatAdministrators($chat->chat_id);
        foreach ($telegramAdministrators as $telegramAdministrator) {
            if ($currentUser->provider_user_id == $telegramAdministrator->getUser()->getId()) {
                $currentUserIsAdmin = true;
            }
            $user = User::findOne(['provider_user_id' => $telegramAdministrator->getUser()->getId()]);

            if (!isset($user)) {
                $user = User::createUser($telegramAdministrator->getUser());
                $user->updateInfo($telegramAdministrator->getUser());
            }

            $administratorUsers[] = $user;

            if (!in_array($user, $currentAdministrators)) {
                $user->link('chats', $chat, ['status' => $telegramAdministrator->getStatus()]);
            }
        }

        foreach ($currentAdministrators as $currentAdministrator) {
            if (!in_array($currentAdministrator, $administratorUsers)) {
                $telegramChatMember = $this->getBotApi()->getChatMember(
                    $chat->chat_id,
                    $currentAdministrator->provider_user_id
                );

                if ($telegramChatMember->isActualChatMember()) {
                    $chatMember = ChatMember::findOne(['chat_id' => $chat->id, 'user_id' => $currentAdministrator->id]);
                    $chatMember->setAttributes([
                        'status' => $telegramChatMember->getStatus(),
                    ]);

                    $chatMember->save();
                } else {
                    $chat->unlink('users', $currentAdministrator, true);
                }
            }
        }

        $telegramChat = $this->getBotApi()->getChat($chat->chat_id);
        if (!$telegramChat) {
            $chat -> unlinkAll('phrases');
            $chat -> unlinkAll('settings');
            $chat -> unlinkAll('users');
            $chat -> delete();
        }

        if (!$currentUserIsAdmin || !$telegramChat) {
            return [];
        }

        return ResponseBuilder::fromUpdate($this->getUpdate())
                ->editMessageTextOrSendMessage(
                    $this->render('index')
                )
                ->build();
    }
}
