<?php
namespace app\modules\bot\components\response\commands;

use Yii;
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
        Yii::warning(new HttpException('test'), 'test1');
        Yii::warning('xxx', 'test1');
        try {
            $answer = $botApi->deleteMessage(
                $this->chatId,
                $this->messageId
            );
        } catch (HttpException $e) {
            Yii::warning($e);
        }

        return $answer;
    }
}
