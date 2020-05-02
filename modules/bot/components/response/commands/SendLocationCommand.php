<?php
namespace app\modules\bot\components\response\commands;

use TelegramBot\Api\BotApi;

class SendLocationCommand extends Command
{
    public function __construct(string $chatId, int $latitude, int $longitude, array $optionalParams = [])
    {
        parent::__construct($optionalParams);

        $this->chatId = $chatId;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    public function send(BotApi $botApi)
    {
        return $botApi->sendLocation(
            $this->chatId,
            $this->latitude,
            $this->longitude,
            $this->getOptionalProperty('replyToMessageId', null),
            $this->getOptionalProperty('replyMarkup', null),
            $this->getOptionalProperty('disableNotification', false),
            $this->getOptionalProperty('livePeriod', null)
        );
    }
}
