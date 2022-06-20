<?php

namespace app\modules\bot\models;

use app\models\User as GlobalUser;
use app\modules\bot\models\queries\ChatQuery;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "bot_chat".
 *
 * @package app\modules\bot\models
 */
class Chat extends ActiveRecord
{
    public const TYPE_PRIVATE = 'private';
    public const TYPE_GROUP = 'group';
    public const TYPE_SUPERGROUP = 'supergroup';
    public const TYPE_CHANNEL = 'channel';

    private array $settings = [];

    public static function tableName()
    {
        return '{{%bot_chat}}';
    }

    public function rules()
    {
        return [
            [['type', 'bot_id', 'chat_id'], 'required'],
            [['id', 'chat_id', 'bot_id'], 'integer'],
            [['type', 'title', 'username', 'first_name', 'last_name', 'description'], 'string'],
            [['timezone'], 'default', 'value' => 0],
            [['timezone'], 'integer', 'min' => -720, 'max' => 840],
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public static function find(): ChatQuery
    {
        return new ChatQuery(get_called_class());
    }

    /**
     * PHP getter magic method.
     *
     * @param string $name property name
     *
     * @return mixed property value
     * @see getAttribute()
     */
    public function __get($name)
    {
        if ($this->hasAttribute($name)) {
            return $this->getAttribute($name);
        }

        if ($this->hasMethod('get' . ucfirst($name))) {
            return $this->{'get' . ucfirst($name)}();
        }

        if (isset($this->settings[$name]) || array_key_exists($name, $this->settings)) {
            return $this->settings[$name]->value;
        }

        $chatSetting = $this->getSettings()
            ->where([
                'setting' => $name,
            ])
            ->one();

        if (!isset($chatSetting)) {
            $chatSetting = new ChatSetting();

            $chatSetting->setAttributes([
                'chat_id' => $this->id,
                'setting' => $name,
                'value' => $chatSetting->getDefault($name),
            ]);
        }

        $this->settings[$name] = &$chatSetting;

        return $this->settings[$name]->value;
    }

    /**
     * PHP setter magic method.
     *
     * @param string $name property name
     * @param mixed $value property value
     * @see setAttribute()
     */
    public function __set($name, $value)
    {
        if ($this->hasAttribute($name)) {
            return $this->setAttribute($name, $value);
        }

        if ($this->hasMethod('set' . ucfirst($name))) {
            return $this->{'set' . ucfirst($name)}($value);
        }

        $chatSetting = $this->getSettings()
            ->where([
                'setting' => $name,
            ])
            ->one();

        if (!isset($chatSetting)) {
            $chatSetting = new ChatSetting();

            $chatSetting->setAttributes([
                'chat_id' => $this->id,
                'setting' => $name,
            ]);
        }

        $chatSetting->value = $value;

        $chatSetting->save();

        $this->settings[$name] = &$chatSetting;
    }

    public function validateSettingValue(string $setting, $value)
    {
        $chatSetting = new ChatSetting();
        $chatSetting->setting = $setting;
        $chatSetting->value = $value;

        if (!$chatSetting->validate('value')) {
            return false;
        }

        return true;
    }

    public function isPrivate()
    {
        return $this->type == self::TYPE_PRIVATE;
    }

    public function isGroup()
    {
        return $this->type == self::TYPE_GROUP || $this->type == self::TYPE_SUPERGROUP;
    }

    public function isChannel()
    {
        return $this->type == self::TYPE_CHANNEL;
    }

    public function getPhrases()
    {
        return $this->hasMany(Phrase::class, ['chat_id' => 'id']);
    }

    public function getBlacklistPhrases()
    {
        return $this->getPhrases()
            ->where([
                'type' => Phrase::TYPE_BLACKLIST,
            ]);
    }

    public function getWhitelistPhrases()
    {
        return $this->getPhrases()
            ->where([
                'type' => Phrase::TYPE_WHITELIST,
            ]);
    }

    public function getQuestionPhrases()
    {
        return $this->hasMany(ChatFaqQuestion::class, ['chat_id' => 'id']);
    }

    public function getSettings()
    {
        return $this->hasMany(ChatSetting::class, ['chat_id' => 'id']);
    }

    public function getSetting($setting)
    {
        $chatSetting = $this->getSettings()
            ->where([
                'setting' => $setting,
            ])
            ->one();

        if (!isset($chatSetting)) {
            $chatSetting = new ChatSetting();

            $chatSetting->setAttributes([
                'chat_id' => $this->id,
                'setting' => $setting,
            ]);
        }

        return $chatSetting;
    }

    public function getUsers()
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])
            ->viaTable('{{%bot_chat_member}}', ['chat_id' => 'id']);
    }

    public function getHumanUsers()
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])
            ->where([
                'is_bot' => 0,
            ])
            ->viaTable('{{%bot_chat_member}}', ['chat_id' => 'id']);
    }

    public function getChatMembers()
    {
        return $this->hasMany(ChatMember::class, ['chat_id' => 'id']);
    }

    public function getChatMemberByUser($user)
    {
        return $this->hasOne(ChatMember::class, ['chat_id' => 'id'])
            ->where([
                'user_id' => $user->id,
            ])
            ->one();
    }

    public function getAdministrators()
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])
            ->viaTable('{{%bot_chat_member}}', ['chat_id' => 'id'], function ($query) {
                $query->andWhere([
                    'or',
                    ['status' => ChatMember::STATUS_CREATOR],
                    ['status' => ChatMember::STATUS_ADMINISTRATOR],
                ]);
            });
    }

    public function getHumanAdministrators()
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])
            ->where([
                'is_bot' => 0,
            ])
            ->viaTable('{{%bot_chat_member}}', ['chat_id' => 'id'], function ($query) {
                $query->andWhere([
                    'or',
                    ['status' => ChatMember::STATUS_CREATOR],
                    ['status' => ChatMember::STATUS_ADMINISTRATOR],
                ]);
            });
    }

    public function getActiveAdministrators()
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])
            ->where([
                'is_bot' => 0,
            ])
            ->viaTable('{{%bot_chat_member}}', ['chat_id' => 'id'], function ($query) {
                $query->andWhere([
                    'or',
                    ['status' => ChatMember::STATUS_CREATOR],
                    ['status' => ChatMember::STATUS_ADMINISTRATOR],
                ])
                ->andWhere([
                    'role' => ChatMember::ROLE_ADMINISTRATOR,
                ]);
            });
    }

    public function getChatId()
    {
        return $this->chat_id;
    }

    /**
     * @param int|string $chatId
     *
     * @throws InvalidArgumentException
     */
    public function setChatId($chatId)
    {
        $this->chat_id = $chatId;
    }

    public function getFilterModeLabel(): string
    {
        return static::getFilterModeLabels()[$this->filter_mode];
    }

    public static function getFilterModeLabels(): array
    {
        return [
            ChatSetting::FILTER_MODE_OFF => Yii::t('bot', 'No list'),
            ChatSetting::FILTER_MODE_BLACKLIST => Yii::t('bot', 'Blacklist'),
            ChatSetting::FILTER_MODE_WHITELIST => Yii::t('bot', 'Whitelist'),
        ];
    }

    /**
     * @param int|null $userId
     *
     * @return ChatMember
     */
    public function getChatMemberByUserId($userId = null)
    {
        if (!$userId) {
            $userId = Yii::$app->getModule('bot')->getUser()->getId();
        }

        return ChatMember::findOne([
            'chat_id' => $this->id,
            'user_id' => $userId,
        ]);
    }

    public function getUsername()
    {
        return $this->username;
    }
}
