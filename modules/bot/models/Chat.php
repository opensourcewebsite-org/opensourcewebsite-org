<?php

namespace app\modules\bot\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class Chat extends ActiveRecord
{
    public const TYPE_PRIVATE = 'private';
    public const TYPE_GROUP = 'group';
    public const TYPE_SUPERGROUP = 'supergroup';
    public const TYPE_CHANNEL = 'channel';

    private array $settings = [];

    public static function tableName()
    {
        return 'bot_chat';
    }

    public function rules()
    {
        return [
            [['type', 'bot_id', 'chat_id'], 'required'],
            [['id', 'chat_id', 'bot_id'], 'integer'],
            [['type', 'title', 'username', 'first_name', 'last_name'], 'string'],
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
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
        return $this->hasMany(BotChatFaqQuestion::class, ['chat_id' => 'id']);
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
}
