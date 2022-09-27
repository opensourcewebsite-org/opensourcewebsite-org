<?php

namespace app\modules\bot\components\response\commands;

use Yii;

class DeleteMessageCommand extends Command
{
    public function __construct($chatId, $messageId)
    {
        parent::__construct();

        $this->chatId = $chatId;
        $this->messageId = $messageId;
    }

    /**
     * @return \TelegramBot\Api\Types\Message
    */
    public function send()
    {
        return $this->getBotApi()->deleteMessage(
            $this->chatId,
            $this->messageId
        );
    }
}
