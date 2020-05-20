<?php

namespace app\modules\bot\components\api\Types;

use TelegramBot\Api\Types\ChatPhotot;
use app\modules\bot\models\Chat as ActiveRecordChat;

class Chat extends \TelegramBot\Api\Types\Chat
{
    /**
     * {@inheritdoc}
     *
     * @var array
     */
    static protected $map = [
        'id' => true,
        'type' => true,
        'title' => true,
        'username' => true,
        'first_name' => true,
        'last_name' => true,
        'all_members_are_administrators' => true,
        'photo' => ChatPhoto::class,
        'description' => true,
        'invite_link' => true,
        'pinned_message' => Message::class,
        'sticker_set_name' => true,
        'can_set_sticker_set' => true
    ];

    public function isPublic()
    {
        return in_array($this->getType(), [ActiveRecordChat::TYPE_GROUP, ActiveRecordChat::TYPE_SUPERGROUP]);
    }
}
