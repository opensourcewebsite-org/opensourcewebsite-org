<?php

declare(strict_types=1);

namespace app\modules\bot\models;

use app\models\Currency;
use app\models\Language;
use app\models\User as GlobalUser;
use app\modules\bot\components\helpers\ExternalLink;
use app\modules\bot\models\queries\ChatQuery;
use DateTime;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "bot_chat".
 *
 * @property int $id
 * @property int $chat_id
 * @property int|null $currency_id
 * @property int|null $language_id
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

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%bot_chat}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['chat_id', 'type'], 'required'],
            [['chat_id', 'currency_id', 'language_id'], 'integer'],
            [['type', 'title', 'username', 'first_name', 'last_name', 'description'], 'string'],
            [['timezone'], 'default', 'value' => 0],
            [['timezone'], 'integer', 'min' => -720, 'max' => 840],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
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
            return parent::__get($name);
        }

        if ($this->hasMethod('get' . ucfirst($name))) {
            return parent::__get($name);
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
            return parent::__set($name, $value);
        }

        if ($this->hasMethod('set' . ucfirst($name))) {
            return parent::__set($name, $value);
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
        return $this->hasMany(ChatPhrase::class, ['chat_id' => 'id']);
    }

    public function getBlacklistPhrases()
    {
        return $this->getPhrases()
            ->where([
                'type' => ChatPhrase::TYPE_BLACKLIST,
            ])
            ->orderBy([
                'text' => SORT_ASC,
            ]);
    }

    public function getWhitelistPhrases()
    {
        return $this->getPhrases()
            ->where([
                'type' => ChatPhrase::TYPE_WHITELIST,
            ])
            ->orderBy([
                'text' => SORT_ASC,
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
            ->viaTable(ChatMember::tableName(), ['chat_id' => 'id']);
    }

    public function getHumanUsers()
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])
            ->human()
            ->viaTable(ChatMember::tableName(), ['chat_id' => 'id']);
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
            ->viaTable(ChatMember::tableName(), ['chat_id' => 'id'], function ($query) {
                $query->andWhere([
                    'or',
                    [ChatMember::tableName() . '.status' => ChatMember::STATUS_CREATOR],
                    [ChatMember::tableName() . '.status' => ChatMember::STATUS_ADMINISTRATOR],
                ]);
            });
    }

    public function getHumanAdministrators()
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])
            ->human()
            ->viaTable(ChatMember::tableName(), ['chat_id' => 'id'], function ($query) {
                $query->andWhere([
                    'or',
                    [ChatMember::tableName() . '.status' => ChatMember::STATUS_CREATOR],
                    [ChatMember::tableName() . '.status' => ChatMember::STATUS_ADMINISTRATOR],
                ]);
            });
    }

    public function getActiveAdministrators()
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])
            ->human()
            ->viaTable(ChatMember::tableName(), ['chat_id' => 'id'], function ($query) {
                $query->andWhere([
                    'or',
                    [ChatMember::tableName() . '.status' => ChatMember::STATUS_CREATOR],
                    [ChatMember::tableName() . '.status' => ChatMember::STATUS_ADMINISTRATOR],
                ])
                ->andWhere([
                    ChatMember::tableName() . '.role' => ChatMember::ROLE_ADMINISTRATOR,
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

    public function hasUsername()
    {
        return (bool)$this->username;
    }

    public function getChatMemberCreator()
    {
        return $this->hasOne(ChatMember::class, ['chat_id' => 'id'])
            ->andWhere([
                ChatMember::tableName() . '.status' => ChatMember::STATUS_CREATOR,
            ]);
    }

    public function getPremiumChatMembers()
    {
        $today = new DateTime('@' . (time() + ($this->timezone * 60)));

        return $this->hasMany(ChatMember::class, ['chat_id' => 'id'])
            ->andWhere([
                '>', ChatMember::tableName() . '.membership_date', $today->format('Y-m-d'),
            ])
            ->andWhere([
                'OR',
                [ChatMember::tableName() . '.limiter_date' => null],
                ['>', ChatMember::tableName() . '.limiter_date', $today->format('Y-m-d')],
            ])
            ->orderByRank();
    }

    public function getChatMembersWithIntro()
    {
        return $this->hasMany(ChatMember::class, ['chat_id' => 'id'])
            ->andWhere([
                'not',
                [ChatMember::tableName() . '.intro' => null],
            ])
            ->orderByRank();
    }

    public function getChatMembersWithPositiveReviews()
    {
        return $this->hasMany(ChatMember::class, ['chat_id' => 'id'])
            ->joinWith('positiveReviews')
            ->groupBy(ChatMember::tableName() . '.id')
            ->orderByRank();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrency()
    {
        return $this->hasOne(Currency::class, ['id' => 'currency_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLanguage()
    {
        return $this->hasOne(Language::class, ['id' => 'language_id']);
    }

    public function getPublisherPosts(): ActiveQuery
    {
        return $this->hasMany(ChatPublisherPost::class, ['chat_id' => 'id']);
    }

    public function isBasicCommandsOn()
    {
        if ($this->isGroup() || $this->isChannel()) {
            if ($this->basic_commands_status == ChatSetting::STATUS_ON) {
                return true;
            }
        }

        return false;
    }

    public function isMembershipOn()
    {
        if ($this->isGroup() || $this->isChannel()) {
            if ($this->membership_status == ChatSetting::STATUS_ON) {
                return true;
            }
        }

        return false;
    }

    public function isSlowModeOn()
    {
        if ($this->isGroup() || $this->isChannel()) {
            if ($this->slow_mode_status == ChatSetting::STATUS_ON) {
                return true;
            }
        }

        return false;
    }

    public function isJoinCaptchaOn()
    {
        if ($this->isGroup() || $this->isChannel()) {
            if ($this->join_captcha_status == ChatSetting::STATUS_ON) {
                return true;
            }
        }

        return false;
    }

    public function isGreetingOn()
    {
        if ($this->isGroup() || $this->isChannel()) {
            if ($this->greeting_status == ChatSetting::STATUS_ON) {
                return true;
            }
        }

        return false;
    }

    public function isJoinHiderOn()
    {
        if ($this->isGroup() || $this->isChannel()) {
            if ($this->join_hider_status == ChatSetting::STATUS_ON) {
                return true;
            }
        }

        return false;
    }

    public function isNotifyNameChangeOn()
    {
        if ($this->isGroup() || $this->isChannel()) {
            if ($this->notify_name_change_status == ChatSetting::STATUS_ON) {
                return true;
            }
        }

        return false;
    }

    public function isPublisherOn()
    {
        if ($this->isGroup() || $this->isChannel()) {
            if ($this->publisher_status == ChatSetting::STATUS_ON) {
                return true;
            }
        }

        return false;
    }

    public function isFaqOn()
    {
        if ($this->isGroup() || $this->isChannel()) {
            if ($this->faq_status == ChatSetting::STATUS_ON) {
                return true;
            }
        }

        return false;
    }

    public function isMessageFilterOn()
    {
        if ($this->isGroup() || $this->isChannel()) {
            if ($this->filter_status == ChatSetting::STATUS_ON) {
                return true;
            }
        }

        return false;
    }

    public function getLink()
    {
        return ExternalLink::getBotStartLink($this->getUsername() ?: $this->getChatId());
    }
}
