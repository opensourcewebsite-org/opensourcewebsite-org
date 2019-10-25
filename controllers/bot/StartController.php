<?php

namespace app\controllers\bot;

use app\components\BotCommandController;
use TelegramBot\Api\Types\Message;

/**
 * Class StartController
 *
 * @package app\controllers\bot
 */
class StartController extends BotCommandController
{

    /** @var Message */
    public $requestMessage = null;

    /**
     * @return string
     */
    public function actionIndex()
    {
        return 'Hi ' . $this->requestMessage->getFrom()->getFirstName();
    }
}