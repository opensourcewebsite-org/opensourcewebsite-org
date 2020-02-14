<?php
namespace app\modules\bot\components\response;

use \TelegramBot\Api\BotApi;

class SendMessageCommand extends Command
{
    public $replyMarkup = null;

    public function __construct($chatId, $text, $optionalParams = [])
    {
        parent::__construct($optionalParams);

        $this->chatId = $chatId;
        $this->text = $text;
    }

    public function send(BotApi $botApi)
    {
        $botApi->sendMessage(
            $this->chatId,
            $this->text,
            $this->getOptionalProperty('parseMode', null),
            $this->getOptionalProperty('disablePreview', false),
            $this->getOptionalProperty('replyToMessageId', null),
            $this->getOptionalProperty('replyMarkup', $this->getReplyMarkup()),
            $this->getOptionalProperty('disableNotification', false)
        );
    }

    /**
     * @return null
     */
    public function getReplyMarkup()
    {
        return $this->replyMarkup;
    }

    /**
     * @param null $replyMarkup
     */
    public function setReplyMarkup($replyMarkup): void
    {
        $this->replyMarkup = $replyMarkup;
    }
}
