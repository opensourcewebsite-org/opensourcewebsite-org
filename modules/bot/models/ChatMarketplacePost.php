<?php

namespace app\modules\bot\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "bot_chat_marketplace_post".
 *
 * @property int $id
 * @property int $user_id
 * @property int $chat_id
 * @property string $text
 * @property int $created_at
 * @property int|null $sent_at
 * @property int|null $provider_message_id
 *
 * @property Chat $chat
 * @property User $user
 *
 * @package app\modules\bot\models
 */
class ChatMarketplacePost extends ActiveRecord
{
    public const STATUS_OFF = 0;
    public const STATUS_ON = 1;
    // minimum seconds between re-posting a post
    public const REPOST_SECONDS_LIMIT = 1 * 60; // seconds

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%bot_chat_marketplace_post}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'chat_id', 'text'], 'required'],
            [['user_id', 'chat_id', 'status', 'created_at', 'sent_at', 'provider_message_id'], 'integer'],
            [['title'], 'string', 'max' => 255],
            [['text'], 'string', 'max' => 10000],
            [['chat_id'], 'exist', 'skipOnError' => true, 'targetClass' => Chat::className(), 'targetAttribute' => ['chat_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'chat_id' => 'Chat ID',
            'status' => Yii::t('app', 'Status'),
            'title' => Yii::t('app', 'Title'),
            'text' => Yii::t('app', 'Text'),
            'created_at' => Yii::t('app', 'Created At'),
            'sent_at' => 'Sent At',
            'provider_message_id' => 'Provider Message ID',
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => false,
            ],
        ];
    }

    /**
     * Gets query for [[Chat]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getChat()
    {
        return $this->hasOne(Chat::className(), ['id' => 'chat_id']);
    }

    public function getChatId()
    {
        return $this->chat_id;
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function isActive(): bool
    {
        return (int)$this->status === static::STATUS_ON;
    }

    public function setActive(): self
    {
        $this->status = static::STATUS_ON;

        return $this;
    }

    public function setInactive(): self
    {
        $this->status = static::STATUS_OFF;

        return $this;
    }

    public function getProviderMessageId()
    {
        return $this->provider_message_id;
    }

    public function canRepost()
    {
        if (!$this->sent_at || (($this->sent_at + self::REPOST_SECONDS_LIMIT) < time())) {
            return true;
        }

        return false;
    }

    public function getRepostSecondsLimit()
    {
        return self::REPOST_SECONDS_LIMIT;
    }
}
