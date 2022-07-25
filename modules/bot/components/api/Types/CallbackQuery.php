<?php

namespace app\modules\bot\components\api\Types;

use app\modules\bot\models\Chat as ChatModel;
use TelegramBot\Api\Types\User;
use TelegramBot\Api\Types\ChatPermissions;
use TelegramBot\Api\Types\ChatLocation;

class CallbackQuery extends \TelegramBot\Api\Types\CallbackQuery
{
    /**
     * {@inheritdoc}
     *
     * @var array
     */
    protected static $map = [
        'id' => true,
        'from' => User::class,
        'message' => Message::class,
        'inline_message_id' => true,
        'chat_instance' => true,
        'data' => true,
        'game_short_name' => true,
    ];
}
