<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "support_group_member".
 *
 * @property int $id
 * @property int $support_group_id
 * @property int $user_id
 * @property int $updated_at
 * @property int $updated_by
 *
 * @property SupportGroup $supportGroup
 * @property User $user
 */
class SupportGroupMember extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'support_group_member';
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
            [['support_group_id', 'user_id'], 'required'],
            [['support_group_id', 'user_id'], 'integer'],
            [['support_group_id'], 'exist', 'skipOnError' => true, 'targetClass' => SupportGroup::className(), 'targetAttribute' => ['support_group_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['user_id'], 'validateMaxCount'],
            [['user_id'], 'unique', 'targetAttribute' => ['user_id', 'support_group_id']],
        ];
    }

     /**
     * Validates the max allowed bot count reached.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validateMaxCount($attribute, $params)
    {
        if (!$this->hasErrors()) {
            if (Yii::$app->user->identity->supportGroupMemberCount >= Yii::$app->user->identity->maxSupportGroupMember) {
                $this->addError($attribute, 'You are not allowed to add more members.');
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
            'support_group_id' => 'Support Group ID',
            'user_id' => 'User ID',
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
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
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
