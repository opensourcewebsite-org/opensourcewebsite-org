<?php
namespace app\modules\bot\components\response;

use \TelegramBot\Api\BotApi;

class EditMessageTextCommand extends Command
{
    public function __construct($chatId, $messageId, $text, $optionalParams = [])
    {
        parent::__construct($optionalParams);

        $this->chatId = $chatId;
        $this->messageId = $messageId;
        $this->text = $text;
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
