<?php

namespace app\modules\bot\models;

use Yii;
use yii\db\ActiveRecord;

class ChatSetting extends ActiveRecord
{
    public const FILTER_STATUS = 'filter_status';
    public const JOIN_HIDER_STATUS = 'join_hider_status';
    public const JOIN_CAPTCHA_STATUS = 'join_captcha_status';
    public const GREETING_STATUS = 'greeting_status';

    public const FILTER_STATUS_ON = 'on';
    public const FILTER_STATUS_OFF = 'off';
    public const FILTER_MODE = 'filter_mode';
    public const FILTER_MODE_BLACKLIST = 'blacklist';
    public const FILTER_MODE_WHITELIST = 'whitelist';

    public const JOIN_HIDER_STATUS_ON = 'on';
    public const JOIN_HIDER_STATUS_OFF = 'off';

    public const JOIN_CAPTCHA_STATUS_ON = 'on';
    public const JOIN_CAPTCHA_STATUS_OFF = 'off';
    public const JOIN_CAPTCHA_LIFETIME_DEFAULT = 300; // seconds

    public const GREETING_STATUS_ON = 'on';
    public const GREETING_STATUS_OFF = 'off';
    public const GREETING_LIFETIME = 'greeting_lifetime';
    public const GREETING_LIFETIME_DEFAULT = 1800; // seconds
    public const GREETING_MESSAGE = 'greeting_message';
    public const GREETING_MESSAGE_LENGHT_MIN = 1;
    public const GREETING_MESSAGE_LENGHT_MAX = 10000;

    public static function tableName()
    {
        return 'bot_chat_setting';
    }

    public function rules()
    {
        return [
            [['chat_id', 'updated_by', 'setting', 'value'], 'required'],
            [['chat_id', 'updated_by'], 'integer'],
            [['setting', 'value'], 'string'],
            [['value'], 'default', 'value' => null],
        ];
    }

    public function behaviors()
    {
        return [
            // TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        // TODO refactoring
        $this->updated_by = Yii::$app->getModule('bot')->getBotUser()->id;

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function beforeValidate()
    {
        // TODO refactoring
        if (empty($this->updated_by)) {
            $this->updated_by = Yii::$app->getModule('bot')->getBotUser()->id;
        }

        return parent::beforeValidate();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBotUser()
    {
        return $this->hasOne(User::class, ['id' => 'updated_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChat()
    {
        return $this->hasOne(Chat::class, ['id' => 'chat_id']);
    }
}
