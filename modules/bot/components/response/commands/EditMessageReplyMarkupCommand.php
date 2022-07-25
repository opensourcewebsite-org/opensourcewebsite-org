<?php

namespace app\modules\bot\components\response\commands;

use Yii;
use app\modules\bot\components\api\BotApi;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use TelegramBot\Api\HttpException;

class EditMessageReplyMarkupCommand extends Command
{
    public function __construct(string $chatId, string $messageId, InlineKeyboardMarkup $replyMarkup = null)
    {
        parent::__construct([]);

        $this->chatId = $chatId;
        $this->messageId = $messageId;
        $this->replyMarkup = $replyMarkup;
    }

    /**
     * @param BotApi $botApi
     * @return \TelegramBot\Api\Types\Message
    */
    public function send(BotApi $botApi)
    {
        $answer = false;

        try {
            $answer = $botApi->editMessageReplyMarkup(
                $this->chatId,
                $this->messageId,
                $this->getOptionalProperty('replyMarkup', null),
                $this->getOptionalProperty('inlineMessageId', null)
            );

            $this->setMessageId($answer->getMessageId());
        } catch (HttpException $e) {
            Yii::warning($e);
        }

        return $answer;
    }
}
