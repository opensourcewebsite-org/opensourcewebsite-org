<?php
namespace app\modules\bot\components\response;

use \TelegramBot\Api\BotApi;

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
