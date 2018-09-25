<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user_user_follow".
 *
 * @property int $followed_user_id
 * @property int $user_id
 *
 * @property User $followedUser
 * @property User $user
 */
class UserUserFollow extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_user_follow';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['followed_user_id', 'user_id'], 'required'],
            [['followed_user_id', 'user_id'], 'integer'],
            [['followed_user_id', 'user_id'], 'unique', 'targetAttribute' => ['followed_user_id', 'user_id']],
            [['followed_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['followed_user_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'followed_user_id' => Yii::t('app', 'Followed User ID'),
            'user_id' => Yii::t('app', 'User ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFollowedUser()
    {
        return $this->hasOne(User::className(), ['id' => 'followed_user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
