<?php

namespace app\modules\bot\models;

use Yii;
use yii\db\ActiveRecord;
use DateTime;

/**
 * This is the model class for table "bot_chat_member".
 *
 * @property int $id
 * @property int $chat_id
 * @property int $user_id
 * @property string $status
 * @property int $role
 * @property int $slow_mode_messages
 * @property int|null $last_message_at
 *
 */
class ChatMember extends ActiveRecord
{
    public const STATUS_CREATOR = 'creator';
    public const STATUS_ADMINISTRATOR = 'administrator';
    public const STATUS_MEMBER = 'member';
    public const STATUS_RESTRICTED = 'restricted';
    public const STATUS_LEFT = 'left';
    public const STATUS_KICKED = 'kicked';

    public const ROLE_ADMINISTRATOR = 2;
    public const ROLE_MEMBER = 1;

    public const ANONYMOUS_ADMINISTRATOR_PROVIDER_USER_ID = 1087968824; // @GroupAnonymousBot user id in groups for anonymous admin
    public const ANONYMOUS_CHANNEL_PROVIDER_USER_ID = 136817688; // @Channel_Bot user id in groups when message is sent on behalf of a channel

    public static function tableName()
    {
        return '{{%bot_chat_member}}';
    }

    public function rules()
    {
        return [
            [['chat_id', 'user_id', 'status', 'role', 'slow_mode_messages'], 'required'],
            [['id', 'chat_id', 'user_id', 'role', 'last_message_at'], 'integer'],
            ['role', 'default', 'value' => 1],
            ['slow_mode_messages', 'default', 'value' => 0],
            [['status'], 'string'],
        ];
    }

    public function behaviors()
    {
        return [
            // TimestampBehavior::className(),
        ];
    }

    public function isCreator()
    {
        return $this->status == self::STATUS_CREATOR;
    }

    public function isAdministrator()
    {
        return $this->status == self::STATUS_CREATOR || $this->status == self::STATUS_ADMINISTRATOR || ($this->isAnonymousAdministrator());
    }

    public function isAnonymousAdministrator()
    {
        return $this->botUser->getProviderUserId() == self::ANONYMOUS_ADMINISTRATOR_PROVIDER_USER_ID;
    }

    public function isAnonymousChannel()
    {
        return $this->botUser->getProviderUserId() == self::ANONYMOUS_CHANNEL_PROVIDER_USER_ID;
    }

    public function isActiveAdministrator()
    {
        return $this->role == self::ROLE_ADMINISTRATOR;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBotUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChat()
    {
        return $this->hasOne(Chat::class, ['id' => 'chat_id']);
    }

    public function getChatId()
    {
        return $this->chat_id;
    }

    public function getUserId()
    {
        return $this->user_id;
    }

    public function getSlowModeMessages()
    {
        return $this->slow_mode_messages;
    }

    public function getLastMessageAt()
    {
        return $this->last_message_at;
    }

    /**
    * @return bool
    */
    public function checkSlowMode()
    {
        if ($chat = $this->chat) {
            if ($this->last_message_at) {
                $today = new DateTime('today');

                if (($today->getTimestamp() + $chat->timezone) <= $this->last_message_at) {
                    if ($chat->slow_mode_messages_limit <= $this->slow_mode_messages) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    public function updateSlowMode($timestamp = null)
    {
        if (!$timestamp) {
            $timestamp = time();
        }

        if ($chat = $this->chat) {
            $today = new DateTime('today');

            if (($today->getTimestamp() + $chat->timezone) <= $this->last_message_at) {
                $this->slow_mode_messages += 1;
            } else {
                $this->slow_mode_messages = 1;
            }

            $this->last_message_at = $timestamp;
            $this->save(false);
        }

        return $this;
    }
}
