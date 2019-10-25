<?php

namespace app\controllers\bot;

use app\components\BotCommandController;

/**
 * Class HelpController
 *
 * @package app\controllers\bot
 */
class HelpController extends BotCommandController
{

    /**
     * @return string
     */
    public function actionIndex()
    {

        return "Bot commands:\n/start\n/help\n";
    }
}