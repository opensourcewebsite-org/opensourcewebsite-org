<?php
namespace app\modules\bot\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class ChatMember extends ActiveRecord
{
    public const STATUS_CREATOR = 'creator';
    public const STATUS_ADMINISTRATOR = 'administrator';

    public static function tableName()
    {
        return 'bot_chat_member';
    }

    public function rules()
    {
        return [
            [['chat_id', 'telegram_user_id', 'status'], 'required'],
            [['id', 'chat_id', 'telegram_user_id'], 'integer'],
            [['status'], 'string'],
        ];
    }

    public function behaviors()
    {
        return [
            // TimestampBehavior::className(),
        ];
    }

    public function isAdmin() {
        return $this->status == self::STATUS_CREATOR || $this->status == self::STATUS_ADMINISTRATOR;
    }
}
