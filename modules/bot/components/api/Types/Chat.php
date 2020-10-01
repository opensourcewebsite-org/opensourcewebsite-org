<?php

namespace app\modules\bot\components\api\Types;

use TelegramBot\Api\Types\ChatPhoto;
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

    public function isPrivate()
    {
        return $this->getType() == ActiveRecordChat::TYPE_PRIVATE;
    }

    public function isGroup()
    {
        return $this->getType() == ActiveRecordChat::TYPE_GROUP || $this->getType() == ActiveRecordChat::TYPE_SUPERGROUP;
    }

    public function isChannel()
    {
        return $this->getType() == ActiveRecordChat::TYPE_CHANNEL;
    }
}
