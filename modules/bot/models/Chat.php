<?php
namespace app\modules\bot\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class Chat extends ActiveRecord
{
    const TYPE_PRIVATE = 'private';
    const TYPE_GROUP = 'group';
    const TYPE_SUPERGROUP = 'supergroup';
    const TYPE_CHANNEL = 'channel'; 

    public static function tableName()
    {
        return 'bot_chat';
    }

    public function rules()
    {
        return [
            [['type', 'bot_id', 'chat_id'], 'required'],
            [['id', 'chat_id', 'bot_id'], 'integer'],
            [['type', 'title', 'username', 'first_name', 'last_name'], 'string'],
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    public function getUsers()
    {
        return $this->hasMany(User::className(), ['id' => 'user_id'])
                    ->viaTable('bot_chat_bot_user', ['chat_id' => 'id']);
    }
}
