<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user_moqup_follow".
 *
 * @property int $moqup_id
 * @property int $user_id
 *
 * @property Moqup $moqup
 * @property User $user
 */
class UserMoqupFollow extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_moqup_follow';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['moqup_id', 'user_id'], 'required'],
            [['moqup_id', 'user_id'], 'integer'],
            [['moqup_id', 'user_id'], 'unique', 'targetAttribute' => ['moqup_id', 'user_id']],
            [['moqup_id'], 'exist', 'skipOnError' => true, 'targetClass' => Moqup::className(), 'targetAttribute' => ['moqup_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'moqup_id' => Yii::t('app', 'Moqup ID'),
            'user_id' => Yii::t('app', 'User ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMoqup()
    {
        return $this->hasOne(Moqup::className(), ['id' => 'moqup_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
