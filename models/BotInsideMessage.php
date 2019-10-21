<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "support_group_inside_message".
 *
 * @property int $id
 * @property int $bot_id
 * @property int $bot_client_id
 * @property int $provider_chat_id
 * @property string $message
 * @property int $created_at
 * @property int $created_by
 *
 * @property BotClient $botClient
 * @property Bot $bot
 */
class BotInsideMessage extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'bot_inside_message';
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
            [['bot_id', 'bot_client_id', 'message'], 'required'],
            [['bot_id', 'bot_client_id', 'created_at', 'updated_at', 'provider_chat_id'], 'integer'],
            [['message'], 'string'],
            [
                ['bot_client_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => BotClient::className(),
                'targetAttribute' => ['bot_client_id' => 'id'],
            ],
            [
                ['bot_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Bot::className(),
                'targetAttribute' => ['bot_id' => 'id'],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'bot_id' => 'Bot ID',
            'bot_client_id' => 'Client ID',
            'provider_chat_id' => 'Provider Chat ID',
            'message' => 'Message',
            'created_at' => 'Created At',

        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBotClient()
    {
        return $this->hasOne(BotClient::className(), ['id' => 'bot_client_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSupportGroupBot()
    {
        return $this->hasOne(Bot::className(), ['id' => 'bot_id']);
    }
}
