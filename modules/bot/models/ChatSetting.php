<?php

namespace app\modules\bot\models;

use Yii;
use yii\db\ActiveRecord;

class ChatSetting extends ActiveRecord
{
    public const STATUS_ON = 'on';
    public const STATUS_OFF = 'off';

    public const JOIN_CAPTCHA_MESSAGE_LIFETIME = 300; // seconds

    public const GREETING_MESSAGE_LIFETIME = 1800; // seconds
    public const GREETING_MESSAGE_LENGHT_MIN = 1;
    public const GREETING_MESSAGE_LENGHT_MAX = 10000;

    public const FILTER_MODE_BLACKLIST = 'blacklist';
    public const FILTER_MODE_WHITELIST = 'whitelist';

    public const FAQ_ANSWER_LENGHT_MIN = 1;
    public const FAQ_ANSWER_LENGHT_MAX = 10000;

    public const STELLAR_THRESHOLD_MIN = 1;
    public const STELLAR_MODE_HOLDERS = 1;
    public const STELLAR_MODE_SIGNERS = 2;

    public static array $settings = [
        'join_hider_status',
        'join_captcha_status',
        'greeting_status',
        'greeting_lifetime',
        'greeting_message',
        'filter_status',
        'filter_mode',
        'faq_status',
        'stellar_status',
        'stellar_asset',
        'stellar_issuer',
        'stellar_threshold',
    ];

    public static array $default_settings = [
        'stellar_threshold' => 1,
    ];

    public static function tableName()
    {
        return 'bot_chat_setting';
    }

    public function rules()
    {
        return [
            [['chat_id', 'updated_by', 'setting', 'value'], 'required'],
            [['chat_id', 'updated_by'], 'integer'],
            [['setting'], 'string'],
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
