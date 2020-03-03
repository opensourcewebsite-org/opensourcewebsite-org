<?php
namespace app\modules\bot\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class Setting extends ActiveRecord
{
    public const FILTER_STATUS = "filter_status";
    public const FILTER_MODE = "filter_mode";

    public const FILTER_MODE_BLACK = "black";
    public const FILTER_MODE_WHITE = "white";

    public const FILTER_STATUS_ON = "on";
    public const FILTER_STATUS_OFF = "off";

    public static function tableName()
    {
        return 'bot_setting';
    }

    public function rules()
    {
        return [
            [['chat_id', 'setting', 'value'], 'required'],
            [['chat_id'], 'integer'],
            [['setting', 'value'], 'string'],
        ];
    }

    public function behaviors()
    {
        return [
            // TimestampBehavior::className(),
        ];
    }
}
