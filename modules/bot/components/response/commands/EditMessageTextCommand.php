<?php

namespace app\modules\bot\components\response\commands;

use app\modules\bot\components\helpers\MessageText;
use TelegramBot\Api\HttpException;
use Yii;

/**
 * Class EditMessageTextCommand
 *
 * @package app\modules\bot\components\response\commands
 */
class EditMessageTextCommand extends MessageTextCommand
{
    public function __construct(string $chatId, string $messageId, MessageText $messageText, array $optionalParams = [])
    {
        parent::__construct($messageText, $optionalParams);

        $this->chatId = $chatId;
        $this->messageId = $messageId;
    }

    /**
     * @return \TelegramBot\Api\Types\Message
    */
    public function send()
    {
        $answer = false;

        try {
            $answer = $this->getBotApi()->editMessageText(
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
