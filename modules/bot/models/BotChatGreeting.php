<?php

namespace app\modules\bot\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "bot_chat_greeting".
 *
 * @property int $id
 * @property int $chat_id
 * @property int $provider_user_id
 * @property int|null $sent_at
 * @property int $message_id
 *
 * @property Chat $chat
 * @property User $providerUser
 */
class BotChatGreeting extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'bot_chat_greeting';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['chat_id', 'provider_user_id', 'message_id'], 'required'],
            [['chat_id', 'provider_user_id', 'sent_at', 'message_id'], 'integer'],
            [['chat_id'], 'exist', 'skipOnError' => true, 'targetClass' => Chat::class, 'targetAttribute' => ['chat_id' => 'id']],
            [['provider_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['provider_user_id' => 'provider_user_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('bot', 'ID'),
            'chat_id' => Yii::t('bot', 'Chat ID'),
            'provider_user_id' => Yii::t('bot', 'Provider User ID'),
            'sent_at' => Yii::t('bot', 'Sent At'),
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'sent_at',
                'updatedAtAttribute' => false
            ],
        ];
    }

    /**
     * Gets query for [[Chat]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getChat()
    {
        return $this->hasOne(Chat::class, ['id' => 'chat_id']);
    }

    /**
     * Gets query for [[ProviderUser]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProviderUser()
    {
        return $this->hasOne(User::class, ['provider_user_id' => 'provider_user_id']);
    }
}
