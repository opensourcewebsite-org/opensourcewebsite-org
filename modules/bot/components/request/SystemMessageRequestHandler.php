<?php
namespace app\modules\bot\components\request;

use app\modules\bot\controllers\publics\SystemMessageController;
use TelegramBot\Api\Types\Update;

class SystemMessageRequestHandler extends MessageRequestHandler
{
    public function getCommandText(Update $update)
    {
        if ($update->getMessage()
            && ($update->getMessage()->getNewChatMember() || $update->getMessage()->getLeftChatMember())) {
            $commandText = SystemMessageController::createRoute();
        }

        if ($update->getMessage() && $update->getMessage()->getMigrateToChatId()) {
            $commandText = SystemMessageController::createRoute('group-to-supergroup');
        }

        return $commandText ?? null;
    }
}
