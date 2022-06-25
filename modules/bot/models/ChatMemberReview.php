<?php

namespace app\modules\bot\models;

use app\models\User as GlobalUser;
use app\modules\bot\components\helpers\Emoji;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "bot_chat_member_review".
 *
 * @property int $id
 * @property int $user_id from
 * @property int $member_id to
 * @property string|null $text
 * @property int $status off, like, dislike
 * @property int $updated_at
 *
 * @property ChatMember $member
 * @property User $user
 */
class ChatMemberReview extends ActiveRecord
{
    public const STATUS_OFF = 0;
    public const STATUS_LIKE = 1;
    public const STATUS_DISLIKE = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'bot_chat_member_review';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'member_id'], 'required'],
            [['user_id', 'member_id', 'updated_at'], 'integer'],
            ['status', 'default', 'value' => self::STATUS_OFF],
            ['status', 'integer', 'min' => 0, 'max' => 2],
            ['text', 'string', 'max' => 10000],
            [['member_id'], 'exist', 'skipOnError' => true, 'targetClass' => ChatMember::className(), 'targetAttribute' => ['member_id' => 'id']],
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
            'member_id' => 'Member ID',
            'text' => 'Text',
            'status' => 'Status',
            'updated_at' => 'Updated At',
        ];
    }

    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => false,
            ],
        ];
    }

    /**
     * Gets query for [[ChatMember]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMember()
    {
        return $this->hasOne(ChatMember::className(), ['id' => 'member_id']);
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

    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Gets query for [[GlobalUser]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGlobalUser()
    {
        return $this->hasOne(GlobalUser::className(), ['id' => 'user_id'])
            ->viaTable(User::tableName(), ['id' => 'user_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCounterUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id'])
            ->viaTable(ChatMember::tableName(), ['id' => 'member_id']);
    }

    public function getStatusLabel(): string
    {
        return static::getStatusLabels()[(int)$this->status];
    }

    public static function getStatusLabels(): array
    {
        return [
            0 => Emoji::PAUSE,
            1 => Emoji::LIKE,
            2 => Emoji::DISLIKE,
        ];
    }

    public function getStatusInfo()
    {
        $array = [
            0 => Yii::t('bot', 'Only you see this review'),
            1 => Yii::t('bot', 'Positive review'),
            2 => Yii::t('bot', 'Negative review'),
        ];

        return $array[(int)$this->status];
    }

    public function isActive(): bool
    {
        return in_array($this->status, [self::STATUS_LIKE, self::STATUS_DISLIKE]);
    }
}
