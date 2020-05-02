<?php
namespace app\modules\bot\components\response\commands;

use app\modules\bot\components\helpers\MessageText;
use TelegramBot\Api\BotApi;

class SendMessageCommand extends MessageTextCommand
{
    public function __construct(string $chatId, MessageText $messageText, array $optionalParams = [])
    {
        parent::__construct($messageText, $optionalParams);

        $this->chatId = $chatId;
    }

    public function send(BotApi $botApi)
    {
        $botApi->sendMessage(
            $this->chatId,
            $this->text,
            $this->getOptionalProperty('parseMode', null),
            $this->getOptionalProperty('disablePreview', false),
            $this->getOptionalProperty('replyToMessageId', null),
            $this->getOptionalProperty('replyMarkup', null),
            $this->getOptionalProperty('disableNotification', false)
        );
    }
}
