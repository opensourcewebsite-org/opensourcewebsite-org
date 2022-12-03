<?php

namespace app\modules\bot\components\api;

use app\modules\bot\components\helpers\MessageText;
use app\modules\bot\models\Chat;
use TelegramBot\Api\HttpException;
use TelegramBot\Api\Types\ChatMember;
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
        Yii::warning('BotApi->deleteMessage()');

        try {
            return parent::deleteMessage($chatId, $messageId);
        } catch (\Exception $e) {
            Yii::warning($e);
        }

        return false;
    }

    /**
    * @param $callbackQueryId
    * @param string|null $text
    * @param bool $showAlert
    * @return bool
     */
    public function answerCallbackQuery($callbackQueryId, $text = null, $showAlert = false, $url = null, $cacheTime = 0)
    {
        Yii::warning('BotApi->answerCallbackQuery()');

        try {
            return parent::answerCallbackQuery(
                $callbackQueryId,
                $text,
                $showAlert,
                $url,
                $cacheTime
            );
        } catch (HttpException $e) {
            Yii::warning($e);
        }

        return false;
    }
}
