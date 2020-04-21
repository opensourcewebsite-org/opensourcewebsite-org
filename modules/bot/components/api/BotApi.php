<?php

namespace app\modules\bot\components\api;

use app\modules\bot\components\api\Types\ChatMember;

/**
 * Class botApi
 *
 * @package app\modules\bot\components\api
 */
class BotApi extends \TelegramBot\Api\BotApi
{
    /**
     * Use this method to get information about a member of a chat.
     *
     * @param string|int $chatId Unique identifier for the target chat or username of the target channel
     *                   (in the format @channelusername)
     * @param int $userId
     *
     * @return ChatMember
     */
    public function getChatMember($chatId, $userId)
    {
        return ChatMember::fromResponse($this->call('getChatMember', [
            'chat_id' => $chatId,
            'user_id' => $userId
        ]));
    }
}
