<?php

namespace app\modules\bot\components\response\commands;

use Yii;
use app\modules\bot\components\helpers\MessageText;
use app\modules\bot\components\helpers\Photo;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\HttpException;

class SendPhotoCommand extends Command
{
    public function __construct(string $chatId, Photo $photo, MessageText $caption, array $optionalParams = [])
    {
        parent::__construct($optionalParams);

        $this->chatId = $chatId;

        if (!is_null($photo)) {
            $this->photo = $photo->getFileId();
        }

        if (!is_null($caption)) {
            $this->caption = $caption->getText();
            $this->parseMode = $caption->getParseMode();
        }
    }

    /**
     * On success, the sent \TelegramBot\Api\Types\Message is returned.
     *
    */
    public function send(BotApi $botApi)
    {
        $answer = false;

        try {
            $answer = $botApi->sendPhoto(
                $this->chatId,
                $this->photo,
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
