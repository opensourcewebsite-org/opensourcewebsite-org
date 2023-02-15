<?php

namespace app\modules\bot\components\api\Types;

use TelegramBot\Api\Types\ChatInviteLink;
use TelegramBot\Api\Types\User;

class ChatJoinRequest extends \TelegramBot\Api\Types\ChatJoinRequest
{
    /**
     * {@inheritdoc}
     *
     * @var array
     */
    protected static $map = [
         'chat' => Chat::class,
         'from' => User::class,
         'user_chat_id' => true,
         'date' => true,
         'bio' => true,
         'invite_link' => ChatInviteLink::class,
     ];
}
