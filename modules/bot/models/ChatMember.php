<?php

namespace app\modules\bot\models;

use app\modules\bot\components\helpers\ExternalLink;
use app\modules\bot\models\queries\ChatMemberQuery;
use DateTime;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "bot_chat_member".
 *
 * @property int $id
 * @property int $chat_id
 * @property int $user_id
 * @property string $status
 * @property int $role
 * @property int $slow_mode_messages
 * @property int $slow_mode_messages_limit
 * @property int|null $last_message_at
 * @property string|null $limiter_date
 * @property string|null membership_date
 * @property string|null intro
 *
 * @package app\modules\bot\models
 */
class ChatMember extends ActiveRecord
{
    public const STATUS_CREATOR = 'creator';
    public const STATUS_ADMINISTRATOR = 'administrator';
    public const STATUS_MEMBER = 'member';
    public const STATUS_RESTRICTED = 'restricted';
    public const STATUS_LEFT = 'left';
    public const STATUS_KICKED = 'kicked';

    public const ROLE_ADMINISTRATOR = 2;
    public const ROLE_MEMBER = 1;

    public const ANONYMOUS_ADMINISTRATOR_PROVIDER_USER_ID = 1087968824; // @GroupAnonymousBot user id in groups for anonymous admin
    public const ANONYMOUS_CHANNEL_PROVIDER_USER_ID = 136817688; // @Channel_Bot user id in groups when message is sent on behalf of a channel

    private array $settings = [
        'join_hider_status' => [
            'active_bot_group_join_hider_quantity_value_per_one_rating',
            'active_bot_group_join_hider_min_quantity_value_per_one_user',
        ],
        'join_captcha_status' => [
            'active_bot_group_join_captcha_quantity_value_per_one_rating',
            'active_bot_group_join_captcha_min_quantity_value_per_one_user',
        ],
        'greeting_status' => [
            'active_bot_group_greeting_quantity_value_per_one_rating',
            'active_bot_group_greeting_min_quantity_value_per_one_user',
        ],
        'membership_status' => [
            'active_bot_group_membership_quantity_value_per_one_rating',
            'active_bot_group_membership_min_quantity_value_per_one_user',
        ],
        'slow_mode_status' => [
            'active_bot_group_slow_mode_quantity_value_per_one_rating',
            'active_bot_group_slow_mode_min_quantity_value_per_one_user',
        ],
        'filter_status' => [
            'active_bot_group_filter_quantity_value_per_one_rating',
            'active_bot_group_filter_min_quantity_value_per_one_user',
        ],
        'limiter_status' => [
            'active_bot_group_limiter_quantity_value_per_one_rating',
            'active_bot_group_limiter_min_quantity_value_per_one_user',
        ],
        'faq_status' => [
            'active_bot_group_faq_quantity_value_per_one_rating',
            'active_bot_group_faq_min_quantity_value_per_one_user',
        ],
        'stellar_status' => [
            'active_bot_group_stellar_quantity_value_per_one_rating',
            'active_bot_group_stellar_min_quantity_value_per_one_user',
        ],
        'marketplace_status' => [
            'active_bot_group_marketplace_quantity_value_per_one_rating',
            'active_bot_group_marketplace_min_quantity_value_per_one_user',
        ],
    ];

    public static function tableName()
    {
        return '{{%bot_chat_member}}';
    }

    public function rules()
    {
        return [
            [['chat_id', 'user_id', 'status', 'role', 'slow_mode_messages'], 'required'],
            [['id', 'chat_id', 'user_id', 'role', 'last_message_at', 'slow_mode_messages_limit'], 'integer'],
            ['role', 'default', 'value' => 1],
            ['slow_mode_messages', 'default', 'value' => 0],
            ['status', 'string'],
            [['limiter_date', 'membership_date'], 'date'],
            ['intro', 'string', 'max' => 10000],
        ];
    }

    public function behaviors()
    {
        return [
            // TimestampBehavior::className(),
        ];
    }

    public static function find(): ChatMemberQuery
    {
        return new ChatMemberQuery(get_called_class());
    }

    public function isCreator()
    {
        return $this->status == self::STATUS_CREATOR;
    }

    public function isAdministrator()
    {
        return $this->status == self::STATUS_CREATOR || $this->status == self::STATUS_ADMINISTRATOR || ($this->isAnonymousAdministrator());
    }

    public function isAnonymousAdministrator()
    {
        return $this->user->getProviderUserId() == self::ANONYMOUS_ADMINISTRATOR_PROVIDER_USER_ID;
    }

    public function isAnonymousChannel()
    {
        return $this->user->getProviderUserId() == self::ANONYMOUS_CHANNEL_PROVIDER_USER_ID;
    }

    public function isActiveAdministrator()
    {
        return $this->role == self::ROLE_ADMINISTRATOR;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChat(): ActiveQuery
    {
        return $this->hasOne(Chat::class, ['id' => 'chat_id']);
    }

    public function getChatId()
    {
        return $this->chat_id;
    }

    public function getUserId()
    {
        return $this->user_id;
    }

    public function getSlowModeMessages()
    {
        return $this->slow_mode_messages;
    }

    public function getLastMessageAt()
    {
        return $this->last_message_at;
    }

    /**
    * @return bool
    */
    public function checkSlowMode()
    {
        if ($chat = $this->chat) {
            if ($this->last_message_at) {
                $today = new DateTime('today');

                if (($today->getTimestamp() - ($chat->timezone * 60)) <= $this->last_message_at) {
                    $slowModeMessagesLimit = $this->slow_mode_messages_limit ?? $chat->slow_mode_messages_limit;

                    if ($slowModeMessagesLimit <= $this->slow_mode_messages) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
    * @return bool
    */
    public function checkLimiter()
    {
        if ($chat = $this->chat) {
            if ($this->limiter_date) {
                $date = new DateTime($this->limiter_date);

                if (($date->getTimestamp() - ($chat->timezone * 60)) <= time()) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
    * @return bool
    */
    public function hasMembership()
    {
        if ($chat = $this->chat) {
            if ($this->membership_date) {
                $date = new DateTime($this->membership_date);

                if (($date->getTimestamp() - ($chat->timezone * 60)) > time()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
    * @return bool
    */
    public function checkMembership()
    {
        if ($chat = $this->chat) {
            if ($this->membership_date) {
                $date = new DateTime($this->membership_date);

                if (($date->getTimestamp() - ($chat->timezone * 60)) <= time()) {
                    return false;
                }
            }
        }

        return true;
    }

    public function updateSlowMode($timestamp = null)
    {
        if (!$timestamp) {
            $timestamp = time();
        }

        if ($chat = $this->chat) {
            $today = new DateTime('today');

            if (($today->getTimestamp() - ($chat->timezone * 60)) <= $this->last_message_at) {
                $this->slow_mode_messages += 1;
            } else {
                $this->slow_mode_messages = 1;
            }

            $this->last_message_at = $timestamp;
            $this->save(false);
        }

        return $this;
    }

    public function trySetChatSetting(string $setting, $value): bool
    {
        if (isset($this->settings[$setting])) {
            $activeModelsCount = $this->user->getAdministratedGroups()
                ->joinWith('settings')
                ->andWhere([
                    'setting' => $setting,
                    'value' => $value,
                ])
                ->count();

            $maxActiveModelsCount = (int)max(floor($this->user->globalUser->getRating() * Yii::$app->settings->{$this->settings[$setting][0]}), Yii::$app->settings->{$this->settings[$setting][1]});

            if ($maxActiveModelsCount <= $activeModelsCount) {
                return false;
            }
        }

        $this->chat->{$setting} = $value;

        return true;
    }

    public function getRequiredRatingForChatSetting(string $setting, $value): int
    {
        if (isset($this->settings[$setting])) {
            $activeModelsCount = $this->user->getAdministratedGroups()
                ->joinWith('settings')
                ->andWhere([
                    'setting' => $setting,
                    'value' => $value,
                ])
                ->count();

            $maxActiveModelsCount = (int)max(floor($this->user->globalUser->getRating() * Yii::$app->settings->{$this->settings[$setting][0]}), Yii::$app->settings->{$this->settings[$setting][1]});

            return (int)ceil(($activeModelsCount + 1) / Yii::$app->settings->{$this->settings[$setting][0]});
        }

        return 1;
    }

    public function getActiveReviews(): ActiveQuery
    {
        return $this->hasMany(ChatMemberReview::class, ['member_id' => 'id'])
            ->andWhere([
                '>', ChatMemberReview::tableName() . '.status', 0,
            ]);
    }

    public function getActiveReviewsCount()
    {
        return $this->getActiveReviews()
            ->count();
    }

    public function getPositiveReviews(): ActiveQuery
    {
        return $this->hasMany(ChatMemberReview::class, ['member_id' => 'id'])
            ->andWhere([
                ChatMemberReview::tableName() . '.status' => ChatMemberReview::STATUS_LIKE,
            ]);
    }

    public function getPositiveReviewsCount()
    {
        return $this->getPositiveReviews()
            ->count();
    }

    public function getNegativeReviews(): ActiveQuery
    {
        return $this->hasMany(ChatMemberReview::class, ['member_id' => 'id'])
            ->andWhere([
                ChatMemberReview::tableName() . '.status' => ChatMemberReview::STATUS_DISLIKE,
            ]);
    }

    public function getNegativeReviewsCount()
    {
        return $this->getNegativeReviews()
            ->count();
    }

    public function getIntro()
    {
        return $this->intro;
    }

    public function getReviewsLink()
    {
        return ExternalLink::getBotStartLink(($this->user->getUsername() ?: $this->user->getProviderUserId()) . '-' . ($this->chat->getUsername() ?: abs($this->chat->getChatId())));
    }

    /**
    * @param ChatMemberPhrase $phrase
    * @return bool
    */
    public function hasPhrase($phrase)
    {
        $chatMemberPhraseExists = ChatMemberPhrase::find()
            ->andWhere([
                'member_id' => $this->id,
                'phrase_id' => $phrase->id,
            ])
            ->exists();

        if ($chatMemberPhraseExists) {
            return true;
        }

        return false;
    }

    /**
    * @param string|null $type ChatPhrase->type
    * @return \yii\db\ActiveQuery
    */
    public function getPhrases($type = ''): ActiveQuery
    {
        $query = $this->hasMany(ChatPhrase::class, ['id' => 'phrase_id'])
            ->viaTable(ChatMemberPhrase::tableName(), ['member_id' => 'id'])
            ->orderBy([ChatPhrase::tableName() . '.text' => SORT_ASC]);

        if ($type) {
            $query = $query->andWhere([
                ChatPhrase::tableName() . '.type' => $type,
            ]);
        }

        return $query;
    }

    public function canUseMarketplace()
    {
        $chat = $this->chat;

        if ($chat->isGroup() || $chat->isChannel()) {
            if ($chat->marketplace_status == ChatSetting::STATUS_ON) {
                if (($chat->marketplace_mode == ChatSetting::MARKETPLACE_MODE_ALL)
                   || (($chat->marketplace_mode == ChatSetting::MARKETPLACE_MODE_MEMBERSHIP)
                       && ($chat->membership_status == ChatSetting::STATUS_ON) && $this->hasMembership())) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getMembershipTag()
    {
        $chat = $this->chat;

        if ($chat->isGroup() || $chat->isChannel()) {
            if (($chat->membership_status == ChatSetting::STATUS_ON) && $chat->membership_tag) {
                if ($this->hasMembership()) {
                    return $chat->membership_tag;
                }
            }
        }

        return false;
    }
}
