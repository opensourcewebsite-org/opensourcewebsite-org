<?php
namespace app\modules\bot\components\response\commands;

use app\modules\bot\components\helpers\MessageText;
use TelegramBot\Api\BotApi;

class EditMessageTextCommand extends MessageTextCommand
{
    public function __construct(string $chatId, string $messageId, MessageText $messageText, array $optionalParams = [])
    {
        parent::__construct($messageText, $optionalParams);

        $this->chatId = $chatId;
        $this->messageId = $messageId;
    }

    public function send(BotApi $botApi)
    {
        return $botApi->editMessageText(
            $this->chatId,
            $this->messageId,
            $this->text,
            $this->getOptionalProperty('parseMode', null),
            $this->getOptionalProperty('disablePreview', false),
            $this->getOptionalProperty('replyMarkup', null),
            $this->getOptionalProperty('inlineMessageId', null)
        );
    }
}
