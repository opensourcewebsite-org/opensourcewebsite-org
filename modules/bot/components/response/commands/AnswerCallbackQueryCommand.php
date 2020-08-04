<?php

namespace app\modules\bot\components\response\commands;

use Yii;
use app\modules\bot\components\helpers\MessageText;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\HttpException;

class AnswerCallbackQueryCommand extends MessageTextCommand
{
    public function __construct(string $callbackQueryId, MessageText $messageText = null, bool $showAlert = false)
    {
        parent::__construct($messageText);

        $this->callbackQueryId = $callbackQueryId;
        $this->showAlert = $showAlert;
    }

    public function send(BotApi $botApi)
    {
        $answer = false;

        try {
            $answer = $botApi->answerCallbackQuery(
                $this->callbackQueryId,
                $this->getOptionalProperty('text', null),
                $this->getOptionalProperty('showAlert', false)
            );
        } catch (HttpException $e) {
            Yii::warning($e);
        }

        return $answer;
    }
}
