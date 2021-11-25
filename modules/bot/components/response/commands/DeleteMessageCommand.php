<?php

namespace app\modules\bot\components\response\commands;

use Yii;
use app\modules\bot\components\api\BotApi;

class DeleteMessageCommand extends Command
{
    public function __construct($chatId, $messageId)
    {
        parent::__construct([]);

        $this->chatId = $chatId;
        $this->messageId = $messageId;
    }

    /**
     * @param BotApi $botApi
     * @return \TelegramBot\Api\Types\Message
    */
    public function send(BotApi $botApi)
    {
        return $botApi->deleteMessage(
            $this->chatId,
            $this->messageId
        );
    }
}
