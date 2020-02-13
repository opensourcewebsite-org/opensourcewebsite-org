<?php
namespace app\module\bot\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class Chat extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%bot_chat}}';
    }

    public function rules()
    {
        return [
            [['id', 'type', 'bot_id'], 'required'],
            [['id', 'bot_id', 'created_at', 'updated_at'], 'integer'],
            [['type', 'title', 'username', 'first_name', 'last_name'], 'string'],
        ];
    }

    public function behabiors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    public function getUsers()
    {
        return $this->hasMany(BotClient::className(), ['id' => 'bot_client_id'])
                    ->viaTable('bot_chat_client', ['bot_chat_id' => 'id']);
    }
}
