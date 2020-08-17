<?php

namespace app\modules\bot\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "bot_chat_greeting_button".
 *
 * @property int $id
 * @property int $chat_id
 * @property string $name
 * @property Chat $chat
 * @property User $updatedBy
 * @property string $value
 * @property int $updated_by
 */
class BotChatGreetingButton extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'bot_chat_greeting_button';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['chat_id', 'name', 'value', 'updated_by'], 'required'],
            [['chat_id', 'updated_by'], 'integer'],
            [['name', 'value'], 'string', 'max' => 255],
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
            'name' => 'Name',
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
