<?php

namespace app\modules\bot\components\api\Types;

use app\modules\bot\models\Chat as ChatModel;
use TelegramBot\Api\Types\ChatPhoto;
use TelegramBot\Api\Types\ChatPermissions;
use TelegramBot\Api\Types\ChatLocation;

class Chat extends \TelegramBot\Api\Types\Chat
{
    /**
     * {@inheritdoc}
     *
     * @var array
     */
    protected static $map = [
        'id' => true,
        'type' => true,
        'title' => true,
        'username' => true,
        'first_name' => true,
        'last_name' => true,
        'photo' => ChatPhoto::class,
        'bio' => true,
        'description' => true,
        'invite_link' => true,
        'pinned_message' => Message::class,
        'permissions' => ChatPermissions::class,
        'slow_mode_delay' => true,
        'sticker_set_name' => true,
        'can_set_sticker_set' => true,
        'linked_chat_id' => true,
        'location' => ChatLocation::class,
    ];

    public function isPrivate()
    {
        return $this->getType() == ChatModel::TYPE_PRIVATE;
    }

    public function isGroup()
    {
        return $this->getType() == ChatModel::TYPE_GROUP || $this->getType() == ChatModel::TYPE_SUPERGROUP;
    }

    public function isChannel()
    {
        return $this->getType() == ChatModel::TYPE_CHANNEL;
    }
}
