<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "support_group_bot".
 *
 * @property int $id
 * @property int $support_group_id
 * @property string $title
 * @property string $token
 * @property int $updated_at
 * @property int $updated_by
 *
 * @property SupportGroup $supportGroup
 * @property SupportGroupClientBot[] $supportGroupClientBots
 * @property SupportGroupInsideMessage[] $supportGroupInsideMessages
 * @property SupportGroupOutsideMessage[] $supportGroupOutsideMessages
 */
class SupportGroupBot extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'support_group_bot';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => false,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['support_group_id', 'title', 'token'], 'required'],
            [['support_group_id'], 'integer'],
            [['title', 'token'], 'string', 'max' => 255],
            [['support_group_id'], 'exist', 'skipOnError' => true, 'targetClass' => SupportGroup::className(), 'targetAttribute' => ['support_group_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'support_group_id' => 'Support Group ID',
            'title' => 'Title',
            'token' => 'Token',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSupportGroup()
    {
        return $this->hasOne(SupportGroup::className(), ['id' => 'support_group_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSupportGroupClientBots()
    {
        return $this->hasMany(SupportGroupClientBot::className(), ['support_group_bot_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSupportGroupInsideMessages()
    {
        return $this->hasMany(SupportGroupInsideMessage::className(), ['support_group_bot_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSupportGroupOutsideMessages()
    {
        return $this->hasMany(SupportGroupOutsideMessage::className(), ['support_group_bot_id' => 'id']);
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $this->updated_by = Yii::$app->user->id;

            return true;
        }
        return false;
    }
}
