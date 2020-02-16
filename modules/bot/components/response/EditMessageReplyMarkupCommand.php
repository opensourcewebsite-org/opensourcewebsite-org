<?php
namespace app\modules\bot\components\response;

use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class EditMessageReplyMarkupCommand extends Command
{
    public function __construct($chatId, $messageId, InlineKeyboardMarkup $replyMarkup = null)
    {
        parent::__construct([]);

        $this->chatId = $chatId;
        $this->messageId = $messageId;
        $this->replyMarkup = $replyMarkup;
    }

    public function send(BotApi $botApi)
    {
        return $botApi->editMessageReplyMarkup(
            $this->chatId,
            $this->messageId,
            $this->getOptionalProperty('replyMarkup', null),
            $this->getOptionalProperty('inlineMessageId', null)
        );
    }
}
