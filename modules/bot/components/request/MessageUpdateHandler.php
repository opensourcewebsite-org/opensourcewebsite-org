<?php
namespace app\modules\bot\components\request;

use TelegramBot\Api\Types\Update;
use app\modules\bot\models\Chat;

class MessageUpdateHandler implements IUpdateHandler
{
    public function getFrom(Update $update)
    {
        if ($message = $update->getMessage() ?? $update->getEditedMessage()) {
            $from = $message->getFrom();
        }

        return $from ?? null;
    }

    public function getChat(Update $update)
    {
        $message = $update->getMessage();

        if ($message) {
            $chat = $message->getChat();
        } elseif ($message = $update->getEditedMessage()) {
            $chat = $message->getChat();

            // Имеет смысл сделать так чтобы бот повторно рассматривал
            // отредактированные сообщения. Это будет влиять на все функции для групп
            // если потребуется для pirvate чата, то это выражение можно убрать
            if (!$chat->isPublic()) {
                unset($chat);
            }
        }
        return $chat ?? null;
    }
}
