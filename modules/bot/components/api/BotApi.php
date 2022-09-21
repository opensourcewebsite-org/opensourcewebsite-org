<?php

namespace app\modules\bot\components\api;

use app\modules\bot\components\api\Types\ChatMember;
use app\modules\bot\models\Chat;
use Yii;

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
     * @param string|int $chatId Unique identifier for the target chat or username of the target channel (in the format @channelusername)
     * @param int $userId
     * @return ChatMember
     */
    public function getChatMember($chatId, $userId)
    {
        try {
            return ChatMember::fromResponse($this->call('getChatMember', [
                'chat_id' => $chatId,
                'user_id' => $userId
            ]));
        } catch (\Exception $e) {
            Yii::warning($e);
        }

        return false;
    }

    /**
     * @param int $chatId
     * @param int $messageId
     * @return bool
     */
    public function deleteMessage($chatId, $messageId)
    {
        try {
            return parent::deleteMessage($chatId, $messageId);
        } catch (\Exception $e) {
            Yii::warning($e);
        }

        return false;
    }
}
