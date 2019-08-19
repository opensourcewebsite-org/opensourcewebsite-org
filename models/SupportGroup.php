<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "support_group".
 *
 * @property int $id
 * @property int $user_id
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
            [['title'], 'required'],
            [['title'], 'unique'],
            [['title'], 'string', 'max' => 255],
            [['title'], 'validateMaxCount'],
        ];
    }

    /**
     * Validates the max allowed group reached.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validateMaxCount($attribute, $params)
    {
        if (!$this->hasErrors()) {
            if ($this->isNewRecord && Yii::$app->user->identity->supportGroupCount >= Yii::$app->user->identity->maxSupportGroup) {
                $this->addError($attribute, 'You are not allowed to add more support groups.');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'title' => 'Name',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
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
                $this->updated_by = Yii::$app->user->id;
            } else {
                $this->updated_by = Yii::$app->user->id;
            }
            return true;
        }
        return false;
    }
}
