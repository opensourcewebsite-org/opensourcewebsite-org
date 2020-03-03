<?php
namespace app\modules\bot\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class ChatSetting extends ActiveRecord
{
    public const FILTER_STATUS = 'filter_status';
    public const FILTER_MODE = 'filter_mode';
    public const JOIN_HIDER_STATUS = 'join_hider_status';

    public const FILTER_MODE_BLACKLIST = 'blacklist';
    public const FILTER_MODE_WHITELIST = 'whitelist';

    public const FILTER_STATUS_ON = 'on';
    public const FILTER_STATUS_OFF = 'off';

    public const JOIN_HIDER_STATUS_ON = 'on';
    public const JOIN_HIDER_STATUS_OFF = 'off';

    public static function tableName()
    {
        return 'bot_chat_setting';
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
