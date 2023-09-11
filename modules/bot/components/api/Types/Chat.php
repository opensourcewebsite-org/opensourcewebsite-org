<?php

namespace app\modules\bot\components\api\Types;

use app\modules\bot\models\Chat as ChatModel;
use TelegramBot\Api\Types\ChatLocation;
use TelegramBot\Api\Types\ChatPermissions;
use TelegramBot\Api\Types\ChatPhoto;

/**
 * Class Chat
 *
 * @package app\modules\bot\components\api\Types
 */
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
        'is_forum' => true,
        'photo' => ChatPhoto::class,
        'active_usernames' => true,
        'emoji_status_custom_emoji_id' => true,
        'bio' => true,
        'has_private_forwards' => true,
        'has_restricted_voice_and_video_messages' => true,
        'join_to_send_messages' => true,
        'join_by_request' => true,
        'description' => true,
        'invite_link' => true,
        'pinned_message' => Message::class,
        'permissions' => ChatPermissions::class,
        'slow_mode_delay' => true,
        'message_auto_delete_time' => true,
        'has_hidden_members' => true,
        'has_protected_content' => true,
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
