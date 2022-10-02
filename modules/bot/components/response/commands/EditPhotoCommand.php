<?php

namespace app\modules\bot\components\response\commands;

use app\modules\bot\components\helpers\MessageText;
use app\modules\bot\components\helpers\Photo;
use TelegramBot\Api\HttpException;
use TelegramBot\Api\Types\InputMedia\InputMediaPhoto;
use Yii;

class EditPhotoCommand extends Command
{
    public function __construct(string $chatId, string $messageId, Photo $photo, MessageText $caption, array $optionalParams = [])
    {
        parent::__construct($optionalParams);

        $this->chatId = $chatId;
        $this->messageId = $messageId;


        if (!is_null($photo)) {
            $this->photo = $photo->getFileId();
        }

        if (!is_null($caption)) {
            $this->caption = $caption->getText();
            $this->parseMode = $caption->getParseMode();
        }

        $this->media = new InputMediaPhoto($this->photo ?: '', $this->caption, $this->parseMode);
    }

    /**
     * @return \TelegramBot\Api\Types\Message
    */
    public function send()
    {
        $answer = false;
        try {
            $answer = $this->getBotApi()->editMessageMedia(
                $this->chatId,
                $this->messageId,
                $this->media,
                $this->getOptionalProperty('inlineMessageId', null),
                $this->getOptionalProperty('replyMarkup', null),
            );
        } catch (HttpException $e) {
            Yii::warning($e);
        }

        return $answer;
    }
}
