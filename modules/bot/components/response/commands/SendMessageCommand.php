<?php

namespace app\modules\bot\components\response\commands;

use Yii;
use app\modules\bot\components\helpers\MessageText;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\HttpException;

class SendMessageCommand extends MessageTextCommand
{
    public function __construct(string $chatId, MessageText $messageText, array $optionalParams = [])
    {
        parent::__construct($messageText, $optionalParams);

        $this->chatId = $chatId;
    }

    /**
     * @param BotApi $botApi
     * @return \TelegramBot\Api\Types\Message
    */
    public function send(BotApi $botApi)
    {
        $answer = false;

        try {
            $answer = $botApi->sendMessage(
                $this->chatId,
                $this->text,
                $this->getOptionalProperty('parseMode', null),
                $this->getOptionalProperty('disablePreview', false),
                $this->getOptionalProperty('replyToMessageId', null),
                $this->getOptionalProperty('replyMarkup', null),
                $this->getOptionalProperty('disableNotification', false)
            );

            $this->setMessageId($answer->getMessageId());
        } catch (HttpException $e) {
            Yii::warning($e);
        }

        return $answer;
    }
}
