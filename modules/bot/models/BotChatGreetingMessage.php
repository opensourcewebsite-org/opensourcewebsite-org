<?php

namespace app\modules\bot\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "bot_chat_greeting_message".
 *
 * @property int $id
 * @property int $chat_id
 * @property string $value
 * @property int $updated_by
 * @property Chat $chat
 * @property User $updatedBy

 */
class BotChatGreetingMessage extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'bot_chat_greeting_message';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['chat_id', 'value', 'updated_by'], 'required'],
            [['chat_id', 'updated_by'], 'integer'],
            [['value'], 'string'],
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
            'value' => 'Value',
            'updated_by' => 'Updated By',
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
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'updated_by']);
    }
}
