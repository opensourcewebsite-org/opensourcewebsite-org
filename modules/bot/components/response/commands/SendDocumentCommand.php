<?php

namespace app\modules\bot\components\response\commands;

use app\modules\bot\components\helpers\Document;
use app\modules\bot\components\helpers\MessageText;
use TelegramBot\Api\HttpException;
use Yii;

/**
 * Class SendDocumentCommand
 *
 * @package app\modules\bot\components\response\commands
 */
class SendDocumentCommand extends Command
{
    public function __construct(string $chatId, Document $document, MessageText $caption, array $optionalParams = [])
    {
        parent::__construct($optionalParams);

        $this->chatId = $chatId;

        if (!is_null($document)) {
            $this->document = $document->getFileId();
        }

        if (!is_null($caption)) {
            $this->caption = $caption->getText();
            $this->parseMode = $caption->getParseMode();
        }
    }

    /**
     * @return \TelegramBot\Api\Types\Message
    */
    public function send()
    {
        $answer = false;

        try {
            $answer = $this->getBotApi()->sendDocument(
                $this->chatId,
                $this->document,
                $this->getOptionalProperty('messageThreadId', null),
                $this->getOptionalProperty('caption', null),
                $this->getOptionalProperty('replyToMessageId', null),
                $this->getOptionalProperty('replyMarkup', null),
                $this->getOptionalProperty('disableNotifications', false),
                $this->getOptionalProperty('parseMode', null)
            );
        } catch (HttpException $e) {
            Yii::warning($e);
        }

        return $answer;
    }
}
