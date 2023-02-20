<?php

declare(strict_types=1);

namespace app\modules\bot\models;

use app\components\helpers\TimeHelper;
use DateTime;
use DateTimeZone;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "bot_chat_publisher_post".
 *
 * @property int $id
 * @property string $text
 * @property int $time minutes (time of day)
 * @property int $chat_id Chat->id
 * @property int topic_id
 * @property int $status
 * @property int $skip_days
 * @property int $created_at
 * @property string|null $title
 * @property int|null $sent_at
 * @property int|null $next_sent_at
 * @property int|null $provider_message_id
 * @property int|null $processed_at
 *
 * @property Chat $chat
 *
 * @package app\modules\bot\models
 */
class ChatPublisherPost extends ActiveRecord
{
    public const STATUS_OFF = 0;
    public const STATUS_ON = 1;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%bot_chat_publisher_post}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['chat_id', 'text'], 'required'],
            [['chat_id', 'topic_id','status', 'time', 'skip_days', 'created_at', 'sent_at', 'next_sent_at', 'provider_message_id', 'processed_at'], 'integer'],
            [['text'], 'string', 'max' => 10000],
            [['time'], 'default', 'value' => rand(0, 1439)],
            [['time'], 'integer', 'min' => 0, 'max' => 1439],
            [['skip_days'], 'default', 'value' => 0],
            [['skip_days'], 'integer', 'min' => 0, 'max' => 365],
            [['status'], 'default', 'value' => 0],
            [['chat_id'], 'exist', 'skipOnError' => true, 'targetClass' => Chat::class, 'targetAttribute' => ['chat_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'chat_id' => 'Chat ID',
            'topic_id' => 'Topic ID',
            'status' => Yii::t('app', 'Status'),
            'text' => Yii::t('app', 'Text'),
            'time' => Yii::t('app', 'Time of day'),
            'skip_days' => Yii::t('app', 'Skip days'),
            'created_at' => Yii::t('app', 'Created At'),
            'sent_at' => 'Sent At',
            'next_sent_at' => 'Next Send At',
            'provider_message_id' => 'Provider Message ID',
            'processed_at' => Yii::t('app', 'Processed At'),
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
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
        return $this->hasOne(Chat::class, ['id' => 'chat_id']);
    }

    public function isActive(): bool
    {
        return $this->status === static::STATUS_ON;
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

    public function getNextSendAt()
    {
        return $this->next_sent_at;
    }

    public function setNextSendAt($timestamp = null)
    {
        if (!$timestamp) {
            $offset = $this->chat->timezone;
            $dateTimeZone = new DateTimeZone(TimeHelper::getTimezoneByOffset($offset));

            if ($this->sent_at) {
                $nextDateTime = new DateTime('@' . $this->sent_at);
                $nextDateTime->setTimezone($dateTimeZone);
                $nextDateTime->setTime(0, 0);
                $nextDateTime->modify('+' . ($this->skip_days + 1) . ' days');
            } else {
                $nextDateTime = new DateTime('today', $dateTimeZone);
            }

            if ($this->time) {
                $nextDateTime->modify('+' . $this->time . 'minutes');
            }

            $nowDateTime = new DateTime('now', $dateTimeZone);

            if ($nowDateTime > $nextDateTime) {
                $dateInterval = $nextDateTime->diff($nowDateTime);
                $nextDateTime->modify('+' . ($dateInterval->format('%a') + 1) . 'days');
            }

            $timestamp = $nextDateTime->getTimestamp();
        }

        $this->next_sent_at = $timestamp;
    }
}
