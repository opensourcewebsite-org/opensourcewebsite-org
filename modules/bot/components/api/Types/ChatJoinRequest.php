<?php

namespace app\modules\bot\components\api\Types;

use app\modules\bot\models\Chat as ChatModel;
use TelegramBot\Api\Types\ChatInviteLink;
use TelegramBot\Api\Types\User;

/**
 * Class ChatJoinRequest
 *
 * @package app\modules\bot\components\api\Types
 */
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

    /**
     * @return ChatModel
     */
    public function getPrivateChat()
    {
        $chat = new ChatModel();

        $chat->setAttributes([
            'chat_id' => $this->getUserChatId(),
            'type' => ChatModel::TYPE_PRIVATE,
        ]);

        return $chat;
    }
}
