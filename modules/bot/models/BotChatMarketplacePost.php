<?php

namespace app\modules\bot\models;

use Yii;

/**
 * This is the model class for table "bot_chat_marketplace_post".
 *
 * @property int $id
 * @property int $user_id
 * @property int $chat_id
 * @property string $text
 * @property int $created_at
 * @property int|null $sent_at
 * @property int|null $provider_message_id
 *
 * @property Chat $chat
 * @property User $user
 */
class BotChatMarketplacePost extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%bot_chat_marketplace_post}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'chat_id', 'text', 'created_at'], 'required'],
            [['user_id', 'chat_id', 'created_at', 'sent_at', 'provider_message_id'], 'integer'],
            [['text'], 'string', 'max' => 10000],
            [['chat_id'], 'exist', 'skipOnError' => true, 'targetClass' => Chat::className(), 'targetAttribute' => ['chat_id' => 'id']],
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
            'chat_id' => 'Chat ID',
            'text' => 'Text',
            'created_at' => 'Created At',
            'sent_at' => 'Sent At',
            'provider_message_id' => 'Provider Message ID',
        ];
    }

    /**
     * Gets query for [[Chat]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getChat()
    {
        return $this->hasOne(Chat::className(), ['id' => 'chat_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
