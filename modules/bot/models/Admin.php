<?php
namespace app\modules\bot\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class Admin extends ActiveRecord
{

    public static function tableName()
    {
        return 'bot_admin';
    }

    public function rules()
    {
        return [
            [['chat_id', 'telegram_user_id'], 'required'],
            [['id', 'chat_id', 'telegram_user_id'], 'integer'],
        ];
    }

    public function behaviors()
    {
        return [
            // TimestampBehavior::className(),
        ];
    }
}
