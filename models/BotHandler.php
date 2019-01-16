<?php

namespace app\models;

use TelegramBot\Api\BotApi;
use Yii;
use yii\helpers\ArrayHelper;
use app\models\SupportGroupCommandText;

/**
 * Class BotHandler
 *
 * @package app\models
 *
 * @property int $chat_id
 * @property string language
 * @property string $command
 * @property bool is_bot
 */
class BotHandler extends BotApi
{

    public $chat_id;
    public $language;
    public $is_bot;
    public $command;

    /**
     * @param \app\models\SupportGroupCommandText[] $commands
     *
     * @return bool
     */
    public function generateResponse($commands)
    {

        if (!$commands) {
            return false;
        }

        $getLanguage = ArrayHelper::map($commands, 'language_code', 'text');

        if (ArrayHelper::keyExists($this->language, $getLanguage)) {
            $output = $getLanguage[$this->language];

            $this->sendMessage($this->chat_id, $output);

            return true;
        }

        # get first command from array;
        $output = $commands[0];

        $this->sendMessage($this->chat_id, $output);

        return true;
    }
}
