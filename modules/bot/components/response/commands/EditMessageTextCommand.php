<?php

namespace app\modules\bot\components\response\commands;

use Yii;
use app\modules\bot\components\helpers\MessageText;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\HttpException;

class EditMessageTextCommand extends MessageTextCommand
{
    public function __construct(string $chatId, string $messageId, MessageText $messageText, array $optionalParams = [])
    {
        parent::__construct($messageText, $optionalParams);

        $this->chatId = $chatId;
        $this->messageId = $messageId;
    }

    /**
     * @param BotApi $botApi
     * @return \TelegramBot\Api\Types\Message
    */
    public function send(BotApi $botApi)
    {
        $answer = false;

        try {
            $answer = $botApi->editMessageText(
                $this->chatId,
                $this->messageId,
                $this->text,
                $this->getOptionalProperty('parseMode', null),
                $this->getOptionalProperty('disablePreview', false),
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
