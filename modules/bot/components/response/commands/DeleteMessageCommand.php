<?php
namespace app\modules\bot\components\response\commands;

use app\modules\bot\components\response\Command;
use TelegramBot\Api\BotApi;

class DeleteMessageCommand extends Command
{
    public function __construct($chatId, $messageId)
    {
        $this->chatId = $chatId;
        $this->messageId = $messageId;
    }

    public function send(BotApi $botApi)
    {
        $botApi->deleteMessage(
            $this->chatId,
            $this->messageId
        );
    }
}
