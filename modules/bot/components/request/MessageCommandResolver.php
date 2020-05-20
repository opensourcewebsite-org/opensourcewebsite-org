<?php

namespace app\modules\bot\components\request;

use TelegramBot\Api\Types\Update;
use app\modules\bot\models\Chat;

class MessageCommandResolver implements ICommandResolver
{
    public function resolveCommand(Update $update)
    {
        if ($message = $update->getMessage()) {
            $commandText = $message->getText();
        }

        if (!isset($commandText) && ($message = $update->getEditedMessage())) {
            $chat = $message->getChat();
            $commandText = $message->getText();
            // Имеет смысл сделать так чтобы бот повторно рассматривал
            // отредактированные сообщения. Это будет влиять на все функции для групп
            // если потребуется для pirvate чата, то это выражение можно убрать
            $isPublicChat = $chat && in_array($chat->getType(), [Chat::TYPE_GROUP, Chat::TYPE_SUPERGROUP]);
            if (!$isPublicChat) {
                unset($chat);
            }
        }

        return $commandText ?? null;
    }
}
