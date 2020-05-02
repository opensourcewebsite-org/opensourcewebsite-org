<?php

namespace app\modules\bot\components\response\commands;

use app\modules\bot\components\helpers\MessageText;

abstract class MessageTextCommand extends Command
{
    public function __construct(MessageText $messageText = null, $array = [])
    {
        parent::__construct($array);

        if (!is_null($messageText)) {
            $this->text = $messageText->getText();
            $this->parseMode = $messageText->getParseMode();
        }
    }
}
