<?php

namespace app\modules\bot\models;

use \yii\db\ActiveRecord;

/**
 * This is the model class for table "ad_greeting".
 *
 * @property int $id
 * @property int $chat_id
 * @property int $provider_user_id
 * @property string|null $greeting_text
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Chat $chat
 * @property User $providerUser
 */
class AdGreeting extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ad_greeting';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['chat_id', 'provider_user_id', 'created_at', 'updated_at'], 'required'],
            [['chat_id', 'provider_user_id', 'created_at', 'updated_at'], 'integer'],
            [['greeting_text'], 'string', 'max' => 255],
            [['chat_id'], 'exist', 'skipOnError' => true, 'targetClass' => Chat::className(), 'targetAttribute' => ['chat_id' => 'id']],
            [['provider_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['provider_user_id' => 'provider_user_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'chat_id' => 'Chat ID',
            'provider_user_id' => 'Provider User ID',
            'greeting_text' => 'Greeting Text',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
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
     * Gets query for [[ProviderUser]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProviderUser()
    {
        return $this->hasOne(User::className(), ['provider_user_id' => 'provider_user_id']);
    }
}
