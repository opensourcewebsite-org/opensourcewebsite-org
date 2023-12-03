<?php

namespace app\modules\bot\components\api;

use app\modules\bot\components\api\Types\ChatMember;
use TelegramBot\Api\HttpException;
use Yii;

/**
 * Class BotApi
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
     * @return ChatMember|false
     */
    public function getChatMember($chatId, $userId)
    {
        Yii::warning('BotApi->getChatMember()');

        try {
            return ChatMember::fromResponse($this->call('getChatMember', [
                'chat_id' => $chatId,
                'user_id' => $userId,
            ]));
        } catch (\Exception $e) {
            Yii::warning($e);
        }

        return false;
    }

    /**
     * Use this method to delete a message, including service messages, with the following limitations:
     *  - A message can only be deleted if it was sent less than 48 hours ago.
     *  - Bots can delete outgoing messages in groups and supergroups.
     *  - Bots granted can_post_messages permissions can delete outgoing messages in channels.
     *  - If the bot is an administrator of a group, it can delete any message there.
     *  - If the bot has can_delete_messages permission in a supergroup or a channel, it can delete any message there.
     *
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
     * Use this method to send answers to callback queries sent from inline keyboards.
     * The answer will be displayed to the user as a notification at the top of the chat screen or as an alert.
     *
     * @param $callbackQueryId
     * @param string|null $text
     * @param bool $showAlert
     * @param string $url
     * @param integer $cacheTime
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

    /**
     * Use this method to restrict a user in a supergroup.
     * The bot must be an administrator in the supergroup for this to work and must have the appropriate admin rights.
     * Pass True for all boolean parameters to lift restrictions from a user.
     *
     * @param string|int $chatId Unique identifier for the target chat or username of the target supergroup
     *                   (in the format @supergroupusername)
     * @param int $userId Unique identifier of the target user
     * @param ChatPermissions $permissions A JSON-serialized object for new user permissions
     * @param bool $useIndependentChatPermissions Optional. Pass True if chat permissions are set independently. Otherwise, the can_send_other_messages and can_add_web_page_previews permissions will imply the can_send_messages, can_send_audios, can_send_documents, can_send_photos, can_send_videos, can_send_video_notes, and can_send_voice_notes permissions; the can_send_polls permission will imply the can_send_messages permission.
     * @param null|integer $untilDate Optional. Date when restrictions will be lifted for the user, unix time.
     *                     If user is restricted for more than 366 days or less than 30 seconds from the current time,
     *                     they are considered to be restricted forever
     * @return bool
     */
    public function restrictChatMember(
        $chatId,
        $userId,
        $permissions = null,
        $useIndependentChatPermissions = false,
        $untilDate = null
    ) {
        Yii::warning('BotApi->restrictChatMember()');

        try {
            return parent::restrictChatMember($chatId, $userId, $permissions, $useIndependentChatPermissions, $untilDate);
        } catch (\Exception $e) {
            Yii::warning($e);
        }

        return false;
    }
}
