<?php

namespace app\modules\bot\controllers\publics;

use app\modules\bot\components\response\commands\SendMessageCommand;
use app\modules\bot\components\Controller as Controller;
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

        $telegramAdministrators = $this->getBotApi()->getChatAdministrators($chat->chat_id);

        $administratorUsers = [];

        $currentAdministrators = $chat->getAdministrators()->all();
        foreach ($telegramAdministrators as $telegramAdministrator) {
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

                $isMember = $telegramChatMember->getIsMember() !== null ? $telegramChatMember->getIsMember() : false;

                if ($isMember) {
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

        return [
            new SendMessageCommand(
                $this->getTelegramChat()->chat_id,
                $this->render('index'),
                [
                    'parseMode' => $this->textFormat,
                    'replyToMessageId' => $this->getUpdate()->getMessage()->getMessageId(),
                ]
            ),
        ];
    }
}
