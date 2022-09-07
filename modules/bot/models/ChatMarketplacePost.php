<?php

namespace app\modules\bot\models;

use app\components\helpers\TimeHelper;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "bot_chat_marketplace_post".
 *
 * @property int $id
 * @property int $member_id
 * @property string $text
 * @property int $time minutes (time of day)
 * @property int $skip_days
 * @property int $created_at
 * @property int|null $sent_at
 * @property int|null $provider_message_id
 * @property int|null $processed_at
 *
 * @property ChatMember $chatMember
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
            [['member_id', 'text'], 'required'],
            [['member_id', 'status', 'time', 'skip_days', 'created_at', 'sent_at', 'provider_message_id', 'processed_at'], 'integer'],
            [['title'], 'string', 'max' => 255],
            [['text'], 'string', 'max' => 10000],
            [['time'], 'default', 'value' => rand(0, 1439)],
            [['time'], 'integer', 'min' => 0, 'max' => 1439],
            [['skip_days'], 'default', 'value' => 0],
            [['skip_days'], 'integer', 'min' => 0, 'max' => 365],
            [['member_id'], 'exist', 'skipOnError' => true, 'targetClass' => ChatMember::className(), 'targetAttribute' => ['member_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'member_id' => 'Member ID',
            'status' => Yii::t('app', 'Status'),
            'title' => Yii::t('app', 'Title'),
            'text' => Yii::t('app', 'Text'),
            'time' => Yii::t('app', 'Time of day'),
            'skip_days' => Yii::t('app', 'Skip days'),
            'created_at' => Yii::t('app', 'Created At'),
            'sent_at' => 'Sent At',
            'provider_message_id' => 'Provider Message ID',
            'processed_at' => Yii::t('app', 'Processed At'),
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
     * Gets query for [[ChatMember]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getChatMember()
    {
        return $this->hasOne(Chat::className(), ['id' => 'chat_id']);
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

    public function getTime()
    {
        return $this->time;
    }

    public function getTimeOfDay()
    {
        return TimeHelper::getTimeOfDayByMinutes($this->time);
    }

    public function getSkipDays()
    {
        return $this->skip_days;
    }

    public function getChatMemberId()
    {
        return $this->member_id;
    }

    /**
     * Gets query for [[Chat]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getChat()
    {
        return $this->hasOne(Chat::class, ['id' => 'chat_id'])
            ->viaTable(ChatMember::tableName(), ['id' => 'member_id']);
    }
}
