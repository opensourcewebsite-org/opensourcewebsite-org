<?php

namespace app\modules\bot\components\api\Types;

use TelegramBot\Api\Types\ChatInviteLink;
use TelegramBot\Api\Types\ChatMember;
use TelegramBot\Api\Types\User;

/**
 * Class ChatMemberUpdated
 *
 * @package app\modules\bot\components\api\Types
 */
class ChatMemberUpdated extends \TelegramBot\Api\Types\ChatMemberUpdated
{
    /**
     * {@inheritdoc}
     *
     * @var array
     */
    protected static $map = [
        'chat' => Chat::class,
        'from' => User::class,
        'date' => true,
        'old_chat_member' => ChatMember::class,
        'new_chat_member' => ChatMember::class,
        'invite_link' => ChatInviteLink::class,
    ];
}
