<?php

namespace app\modules\bot\components\response\commands;

use app\modules\bot\components\helpers\MessageText;
use TelegramBot\Api\HttpException;
use Yii;

/**
 * Class AnswerCallbackQueryCommand
 *
 * @package app\modules\bot\components\response\commands
 */
class AnswerCallbackQueryCommand extends MessageTextCommand
{
    public function __construct(string $callbackQueryId, MessageText $messageText = null, bool $showAlert = false)
    {
        parent::__construct($messageText);

        $this->callbackQueryId = $callbackQueryId;
        $this->showAlert = $showAlert;
    }

    /**
     * @return \TelegramBot\Api\Types\Message
    */
    public function send()
    {
        return $this->getBotApi()->answerCallbackQuery(
            $this->callbackQueryId,
            $this->getOptionalProperty('text', null),
            $this->getOptionalProperty('showAlert', false)
        );
    }
}
