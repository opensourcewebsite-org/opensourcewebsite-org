<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "setting_value_vote".
 *
 * @property int $id
 * @property int $user_id
 * @property int $setting_value_id
 * @property int $setting_id
 * @property int $created_at
 *
 * @property Setting $setting
 * @property SettingValue $settingValue
 * @property User $user
 */
class SettingValueVote extends \yii\db\ActiveRecord
{
    protected $rating = null;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'setting_value_vote';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'setting_value_id', 'setting_id'], 'required'],
            [['user_id', 'setting_value_id', 'setting_id', 'created_at'], 'integer'],
            [['setting_id'], 'exist', 'skipOnError' => true, 'targetClass' => Setting::className(), 'targetAttribute' => ['setting_id' => 'id']],
            [['setting_value_id'], 'exist', 'skipOnError' => true, 'targetClass' => SettingValue::className(), 'targetAttribute' => ['setting_value_id' => 'id']],
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
            'setting_value_id' => 'Setting Value ID',
            'setting_id' => 'Setting ID',
            'created_at' => Yii::t('app', 'Created At'),
        ];
    }

    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => false,
            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSetting()
    {
        return $this->hasOne(Setting::className(), ['id' => 'setting_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSettingValue()
    {
        return $this->hasOne(SettingValue::className(), ['id' => 'setting_value_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return integer vote rating
     */
    public function getRating()
    {
        if (is_null($this->rating)) {
            $this->rating = $this->user->getRating();
        }

        return $this->rating;
    }

    public function getUserId()
    {
        return $this->user_id;
    }

    public function setUserId(int $userId)
    {
        $this->user_id = $userId;
    }

    public function getSettingValueId()
    {
        return $this->setting_value_id;
    }

    public function getSettingId()
    {
        return $this->setting_id;
    }
}
