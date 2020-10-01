<?php

namespace app\modules\bot\components\response\commands;

use Yii;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\HttpException;

class SendLocationCommand extends Command
{
    public function __construct(string $chatId, int $latitude, int $longitude, array $optionalParams = [])
    {
        parent::__construct($optionalParams);

        $this->chatId = $chatId;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    /**
     * @param BotApi $botApi
     * @return \TelegramBot\Api\Types\Message
    */
    public function send(BotApi $botApi)
    {
        $answer = false;

        try {
            $answer = $botApi->sendLocation(
                $this->chatId,
                $this->latitude,
                $this->longitude,
                $this->getOptionalProperty('replyToMessageId', null),
                $this->getOptionalProperty('replyMarkup', null),
                $this->getOptionalProperty('disableNotification', false),
                $this->getOptionalProperty('livePeriod', null)
            );
            $this->setMessageId($answer->getMessageId());
        } catch (HttpException $e) {
            Yii::warning($e);
        }

        return $answer;
    }
}
