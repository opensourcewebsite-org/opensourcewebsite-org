<?php
namespace app\modules\bot\components\response\commands;

use TelegramBot\Api\BotApi;

class DeleteMessageCommand extends Command
{
    public function __construct($chatId, $messageId)
    {
        parent::__construct([]);

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
