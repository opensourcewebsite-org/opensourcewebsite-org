<?php

namespace app\models;

use Yii;

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
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['support_group_id', 'user_id', 'updated_at', 'updated_by'], 'required'],
            [['support_group_id', 'user_id', 'updated_at', 'updated_by'], 'integer'],
            [['support_group_id'], 'exist', 'skipOnError' => true, 'targetClass' => SupportGroup::className(), 'targetAttribute' => ['support_group_id' => 'id']],
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
}
