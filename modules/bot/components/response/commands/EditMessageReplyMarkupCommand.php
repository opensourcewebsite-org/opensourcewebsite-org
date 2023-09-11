<?php

namespace app\modules\bot\components\response\commands;

use TelegramBot\Api\HttpException;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use Yii;

/**
 * Class EditMessageReplyMarkupCommand
 *
 * @package app\modules\bot\components\response\commands
 */
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
     * @return \TelegramBot\Api\Types\Message
    */
    public function send()
    {
        $answer = false;

        try {
            $answer = $this->getBotApi()->editMessageReplyMarkup(
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
