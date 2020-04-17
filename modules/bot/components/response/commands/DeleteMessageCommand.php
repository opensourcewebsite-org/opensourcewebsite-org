<?php
namespace app\modules\bot\components\response\commands;

use TelegramBot\Api\BotApi;
use TelegramBot\Api\HttpException;

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
        $answer=false;

        try {
            $answer = $botApi->deleteMessage(
                $this->chatId,
                $this->messageId
            );
        } catch (HttpException $e) {
            if (YII_ENV_DEV) {
                throw $e;
            }
        }

        return $answer;
    }
}
