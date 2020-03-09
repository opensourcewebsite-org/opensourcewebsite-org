<?php
namespace app\modules\bot\components\request;

use TelegramBot\Api\Types\Update;

class SystemMessageRequestHandler extends MessageRequestHandler
{
    public function getCommandText(Update $update)
    {
        if ($update->getMessage() && ($update->getMessage()->getNewChatMember() || $update->getMessage()->getLeftChatMember())) {
            $commandText = '/system_message';
        }

        if ($update->getMessage() && $update->getMessage()->getMigrateToChatId()) {
            $commandText = '/system_message_group_to_supergroup';
        }

        return $commandText ?? null;
    }
}
