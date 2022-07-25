<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "support_group_inside_message".
 *
 * @property int $id
 * @property int $support_group_bot_id
 * @property int $support_group_bot_client_id
 * @property string $message
 * @property int $created_at
 * @property int $created_by
 *
 * @property SupportGroupBotClient $supportGroupBotClient
 * @property SupportGroupBot $supportGroupBot
 */
class SupportGroupInsideMessage extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'support_group_inside_message';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'updatedAtAttribute' => false,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['support_group_bot_id', 'support_group_bot_client_id'], 'required'],
            [['support_group_bot_id', 'support_group_bot_client_id', 'created_at', 'created_by'], 'integer'],
            [['message'], 'required', 'enableClientValidation' => false],
            [['message'], 'string', 'enableClientValidation' => false],
            [['support_group_bot_client_id'], 'exist', 'skipOnError' => true, 'targetClass' => SupportGroupBotClient::className(), 'targetAttribute' => ['support_group_bot_client_id' => 'id']],
            [['support_group_bot_id'], 'exist', 'skipOnError' => true, 'targetClass' => SupportGroupBot::className(), 'targetAttribute' => ['support_group_bot_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'support_group_bot_id' => 'Support Group Bot ID',
            'support_group_bot_client_id' => 'Support Group Bot Client ID',
            'message' => 'Message',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSupportGroupBotClient()
    {
        return $this->hasOne(SupportGroupBotClient::className(), ['id' => 'support_group_bot_client_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSupportGroupBot()
    {
        return $this->hasOne(SupportGroupBot::className(), ['id' => 'support_group_bot_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->created_by = Yii::$app->user->id;

                return true;
            }

            return false;
        }
    }
}
