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

    public const FILTER_MODE_OFF = 'off';
    public const FILTER_MODE_BLACKLIST = 'blacklist';
    public const FILTER_MODE_WHITELIST = 'whitelist';

    public const FAQ_ANSWER_LENGHT_MIN = 1;
    public const FAQ_ANSWER_LENGHT_MAX = 10000;

    public const STELLAR_THRESHOLD_MIN = 0.00000001;
    public const STELLAR_MODE_HOLDERS = 1;
    public const STELLAR_MODE_SIGNERS = 2;

    public static array $settings = [
        'join_hider_status' => [
            'default' => self::STATUS_OFF,
        ],
        'join_captcha_status' => [
            'default' => self::STATUS_OFF,
        ],
        'greeting_status' => [
            'default' => self::STATUS_OFF,
        ],
        'greeting_lifetime' => [],
        'greeting_message' => [
            'type' => 'string',
            'min' => 1,
            'max' => 10000,
        ],
        'filter_status' => [
            'default' => self::STATUS_OFF,
        ],
        'filter_mode' => [
            'default' => self::FILTER_MODE_OFF,
        ],
        'filter_remove_reply' => [
            'default' => self::STATUS_OFF,
        ],
        'filter_remove_username' => [
            'default' => self::STATUS_OFF,
        ],
        'filter_remove_emoji' => [
            'default' => self::STATUS_OFF,
        ],
        'filter_remove_empty_line' => [
            'default' => self::STATUS_OFF,
        ],
        'faq_status' => [
            'default' => self::STATUS_OFF,
        ],
        'stellar_status' => [
            'default' => self::STATUS_OFF,
        ],
        'stellar_mode' => [
            'default' => self::STELLAR_MODE_HOLDERS,
        ],
        'stellar_asset' => [],
        'stellar_issuer' => [],
        'stellar_threshold' => [
            'default' => 1,
        ],
        'stellar_invite_link' => [],
        'marketplace_status' => [
            'default' => self::STATUS_OFF,
        ],
        'marketplace_active_post_limit_per_member' => [
            'default' => 1,
            'min' => 1,
            'max' => 1000,
        ],
        'marketplace_text_hint' => [
            'type' => 'string',
            'min' => 1,
            'max' => 10000,
        ],
    ];

    public static function tableName()
    {
        return '{{%bot_chat_setting}}';
    }

    public function rules()
    {
        return [
            [['chat_id', 'updated_by', 'setting', 'value'], 'required'],
            [['chat_id', 'updated_by'], 'integer'],
            [['setting'], 'string'],
            ['value', 'trim'],
            ['value', 'validateValue'],
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

    public function getValidationRules()
    {
        return self::$settings[$this->setting] ?? null;
    }

    /**
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validateValue($attribute, $params)
    {
        $rules = $this->getValidationRules();

        if ($rules) {
            if (isset($rules['type'])) {
                switch ($rules['type']) {
                    case 'integer':
                        $this->value = intval($this->value);
                        if (!is_int($this->value)) {
                            $this->addError('value', 'Value must be an integer.');
                        }
                    break;
                    case 'float':
                        $this->value = floatval($this->value);
                        if (!is_float($this->value)) {
                            $this->addError('value', 'Value must be a number.');
                        }
                    break;
                }
            }

            if (isset($rules['min'])) {
                if (isset($rules['type']) && ($rules['type'] == 'string')) {
                    $lenght = mb_strlen($this->value, 'UTF-8');

                    if ($lenght < $rules['min']) {
                        $this->addError('value', 'Text lenght must be no less than ' . $rules['min'] . '.');
                    }
                } else {
                    if ($this->value < $rules['min']) {
                        $this->addError('value', 'Value must be no less than ' . $rules['min'] . '.');
                    }
                }
            }

            if (isset($rules['max'])) {
                if (isset($rules['type']) && ($rules['type'] == 'string')) {
                    $lenght = mb_strlen($this->value, 'UTF-8');

                    if ($lenght > $rules['max']) {
                        $this->addError('value', 'Text lenght must be no greater than ' . $rules['max'] . '.');
                    }
                } else {
                    if ($this->value > $rules['max']) {
                        $this->addError('value', 'Value must be no greater than ' . $rules['max'] . '.');
                    }
                }
            }

            if (isset($rules['less'])) {
                if ($this->value >= $rules['less']) {
                    $this->addError('value', 'Value must be less than ' . $rules['less'] . '.');
                }
            }

            if (isset($rules['more'])) {
                if ($this->value <= $rules['more']) {
                    $this->addError('value', 'Value must be greater than ' . $rules['more'] . '.');
                }
            }
        }
    }

    public function getDefault($name)
    {
        return self::$settings[$name]['default'] ?? null;
    }

    public function getChatId()
    {
        return $this->chat_id;
    }
}
