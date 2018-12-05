<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "support_group".
 *
 * @property int $id
 * @property int $user_id
 * @property string $language_code
 * @property string $title
 * @property int $updated_at
 * @property int $updated_by
 *
 * @property Language $languageCode
 * @property SupportGroupBot[] $supportGroupBots
 * @property SupportGroupClient[] $supportGroupClients
 * @property SupportGroupCommand[] $supportGroupCommands
 * @property SupportGroupMember[] $supportGroupMembers
 */
class SupportGroup extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'support_group';
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
            [['language_code', 'title'], 'required'],
            [['title'], 'unique'],
            [['language_code', 'title'], 'string', 'max' => 255],
            [['language_code'], 'exist', 'skipOnError' => true, 'targetClass' => Language::className(), 'targetAttribute' => ['language_code' => 'code']],
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
            'language_code' => 'Language Code',
            'title' => 'Name',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLanguageCode()
    {
        return $this->hasOne(Language::className(), ['code' => 'language_code']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSupportGroupBots()
    {
        return $this->hasMany(SupportGroupBot::className(), ['support_group_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSupportGroupClients()
    {
        return $this->hasMany(SupportGroupClient::className(), ['support_group_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSupportGroupCommands()
    {
        return $this->hasMany(SupportGroupCommand::className(), ['support_group_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSupportGroupMembers()
    {
        return $this->hasMany(SupportGroupMember::className(), ['support_group_id' => 'id']);
    }


    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->user_id = Yii::$app->user->id;
            } else {
                $this->updated_by = Yii::$app->user->id;
            }
            return true;
        }
        return false;
    }
}
