<?php
namespace app\modules\bot\models;

use yii\db\ActiveRecord;

class ChatSetting extends ActiveRecord
{
    public const FILTER_STATUS = 'filter_status';
    public const FILTER_MODE = 'filter_mode';
    public const JOIN_HIDER_STATUS = 'join_hider_status';
    public const VOTE_BAN_STATUS = 'vote_ban_status';
    public const VOTE_BAN_LIMIT = 'vote_ban_limit';
    public const STAR_TOP_STATUS = 'top_list_status';
    public const JOIN_CAPTCHA_STATUS = 'join_captcha_status';

    public const FILTER_MODE_BLACKLIST = 'blacklist';
    public const FILTER_MODE_WHITELIST = 'whitelist';

    public const FILTER_STATUS_ON = 'on';
    public const FILTER_STATUS_OFF = 'off';

    public const JOIN_HIDER_STATUS_ON = 'on';
    public const JOIN_HIDER_STATUS_OFF = 'off';

    public const VOTE_BAN_STATUS_ON = 'on';
    public const VOTE_BAN_STATUS_OFF = 'off';

    public const VOTE_BAN_LIMIT_DEFAULT = 5;
    public const VOTE_BAN_LIMIT_MIN = 2;
    public const VOTE_BAN_LIMIT_MAX = 100;

    public const STAR_TOP_STATUS_ON = 'on';
    public const STAR_TOP_STATUS_OFF = 'off';

    public const JOIN_CAPTCHA_STATUS_OFF = 'off';
    public const JOIN_CAPTCHA_STATUS_ON = 'on';
    public const JOIN_CAPTCHA_RESPONSE_AWAIT = 300; // seconds

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
