<?php
namespace app\modules\bot\components\response;

use \TelegramBot\Api\BotApi;

class AnswerCallbackQueryCommand extends Command
{
    public function __construct($callbackQueryId, $optionalParams = [])
    {
        parent::__construct($optionalParams);

        $this->callbackQueryId = $callbackQueryId;
    }

    public function send(BotApi $botApi)
    {
        return $botApi->answerCallbackQuery(
            $this->callbackQueryId,
            $this->getOptionalProperty('text', null),
            $this->getOptionalProperty('showAlert', false)
        );
    }
}
