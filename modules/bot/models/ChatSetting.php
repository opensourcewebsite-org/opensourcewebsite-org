<?php

declare(strict_types=1);

namespace app\modules\bot\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "bot_chat_setting".
 *
 * @package app\modules\bot\models
 */
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

    public const STELLAR_MODE_HOLDERS = 1;
    public const STELLAR_MODE_SIGNERS = 2;

    public const MARKETPLACE_MODE_ALL = 1;
    public const MARKETPLACE_MODE_MEMBERSHIP = 2;

    public static array $settings = [
        'basic_commands_status' => [
            'default' => self::STATUS_ON,
        ],
        'join_hider_status' => [
            'default' => self::STATUS_OFF,
        ],
        'filter_remove_member_joined' => [
            'default' => self::STATUS_ON,
        ],
        'filter_remove_member_left' => [
            'default' => self::STATUS_ON,
        ],
        'filter_remove_video_chat_scheduled' => [
            'default' => self::STATUS_OFF,
        ],
        'filter_remove_video_chat_started' => [
            'default' => self::STATUS_OFF,
        ],
        'filter_remove_video_chat_ended' => [
            'default' => self::STATUS_OFF,
        ],
        'filter_remove_video_chat_invited' => [
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
        'membership_status' => [
            'default' => self::STATUS_OFF,
        ],
        'membership_tag' => [
            'type' => 'string',
            'min' => 1,
            'max' => 255,
        ],
        'slow_mode_status' => [
            'default' => self::STATUS_OFF,
        ],
        'slow_mode_messages_limit' => [
            'type' => 'integer',
            'default' => 1,
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
        'filter_remove_channels' => [
            'default' => self::STATUS_OFF,
        ],
        'filter_remove_locations' => [
            'default' => self::STATUS_OFF,
        ],
        'filter_remove_styled_texts' => [
            'default' => self::STATUS_OFF,
        ],
        'limiter_status' => [
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
            'type' => 'float',
            'default' => 1,
            'min' => 0.00000001,
        ],
        'stellar_invite_link' => [],
        'marketplace_status' => [
            'default' => self::STATUS_OFF,
        ],
        'marketplace_mode' => [
            'default' => self::MARKETPLACE_MODE_ALL,
        ],
        // TODO switch to Setting
        'marketplace_active_post_limit_per_member' => [
            'default' => 1,
            'min' => 1,
            'max' => 100,
        ],
        'marketplace_text_hint' => [
            'type' => 'string',
            'min' => 1,
            'max' => 10000,
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%bot_chat_setting}}';
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
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
        $this->updated_by = Yii::$app->getModule('bot')->getUser()->getId();

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function beforeValidate()
    {
        // TODO refactoring
        if (empty($this->updated_by)) {
            $this->updated_by = Yii::$app->getModule('bot')->getUser()->getId();
        }

        return parent::beforeValidate();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
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
