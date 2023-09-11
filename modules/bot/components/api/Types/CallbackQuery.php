<?php

namespace app\modules\bot\components\api\Types;

use TelegramBot\Api\Types\User;

/**
 * Class CallbackQuery
 *
 * @package app\modules\bot\components\api\Types
 */
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
