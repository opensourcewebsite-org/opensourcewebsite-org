<?php

declare(strict_types=1);

namespace app\modules\bot\models;

use app\models\Contact;
use app\models\Language;
use app\models\queries\ContactQuery;
use app\models\User as GlobalUser;
use app\models\UserLocation;
use app\models\Wallet;
use app\modules\bot\components\helpers\MessageText;
use app\modules\bot\components\response\ResponseBuilder;
use app\modules\bot\controllers\privates\DeleteMessageController;
use app\modules\bot\models\queries\UserQuery;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "bot_user".
 *
 * @property int $id
 * @property int $provider_user_id
 * @property string $provider_user_name
 * @property bool $provider_user_blocked
 * @property string $provider_user_first_name
 * @property string $provider_user_last_name
 * @property string $language_code
 * @property int $user_id
 * @property string $currency_code
 * @property string $state
 * @property int $language_id
 * @property bool $is_bot
 * @property int $captcha_confirmed_at
 *
 * @package app\modules\bot\models
 */
class User extends ActiveRecord
{
    // @GroupAnonymousBot user id in groups for anonymous admin
    public const ANONYMOUS_ADMINISTRATOR_PROVIDER_USER_ID = 1087968824;
    // @Channel_Bot user id in groups when message is sent on behalf of a channel
    public const ANONYMOUS_CHANNEL_PROVIDER_USER_ID = 136817688;
    // Service user id, that also acts as sender of channel posts forwarded to discussion groups
    public const ANONYMOUS_LINKED_CHANNEL_PROVIDER_USER_ID = 777000;

    private $stateObject;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%bot_user}}';
    }

    public static function createUser($updateUser)
    {
        $language = Language::findOne([
            'code' => $updateUser->getLanguageCode(),
        ]);

        if (!isset($language)) {
            $language = Language::findOne([
                'code' => 'en',
            ]);
        }

        $newUser = new User();

        $newUser->setAttributes([
            'provider_user_id' => $updateUser->getId(),
            'language_id' => $language->id,
        ]);

        $newUser->save();

        return $newUser;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['provider_user_id'], 'required'],
            [['user_id', 'provider_user_id', 'provider_user_blocked', 'language_id', 'is_bot', 'captcha_confirmed_at'], 'integer'],
            [['provider_user_name', 'provider_user_first_name', 'provider_user_last_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'provider_user_id' => 'Provider User ID',
            'provider_user_name' => 'Username',
            'provider_user_blocked' => 'Provider User Blocked',
            'provider_user_first_name' => 'First name',
            'provider_user_last_name' => 'Last name',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function find(): UserQuery
    {
        return new UserQuery(get_called_class());
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        if (!empty($this->provider_user_first_name) && !empty($this->provider_user_last_name)) {
            return $this->provider_user_first_name . ' ' . $this->provider_user_last_name;
        }
        if (!empty($this->provider_user_first_name)) {
            return $this->provider_user_first_name;
        }
        if (!empty($this->provider_user_last_name)) {
            return $this->provider_user_last_name;
        }

        return '';
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        if ($this->provider_user_name) {
            $name = '@' . $this->provider_user_name;
        } else {
            $name = '#' . $this->user_id;
        }

        $name .= ($this->getFullName() ? ' - ' . $this->getFullName() : '');

        return $name;
    }

    public function getChats()
    {
        return $this->hasMany(Chat::class, ['id' => 'chat_id'])
            ->viaTable(ChatMember::tableName(), ['user_id' => 'id']);
    }

    public function getGroups()
    {
        return $this->hasMany(Chat::class, ['id' => 'chat_id'])
            ->group()
            ->viaTable(ChatMember::tableName(), ['user_id' => 'id']);
    }

    public function getPublicGroups()
    {
        return $this->hasMany(Chat::class, ['id' => 'chat_id'])
            ->group()
            ->hasUsername()
            ->viaTable(ChatMember::tableName(), ['user_id' => 'id']);
    }

    public function getChannels()
    {
        return $this->hasMany(Chat::class, ['id' => 'chat_id'])
            ->channel()
            ->viaTable(ChatMember::tableName(), ['user_id' => 'id']);
    }

    public function getPublicChannels()
    {
        return $this->hasMany(Chat::class, ['id' => 'chat_id'])
            ->channel()
            ->hasUsername()
            ->viaTable(ChatMember::tableName(), ['user_id' => 'id']);
    }

    /**
     * Gets query for private [[Chat]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getChat()
    {
        return $this->hasOne(Chat::class, ['id' => 'chat_id'])
            ->private()
            ->viaTable(ChatMember::tableName(), ['user_id' => 'id']);
    }

    public function getChatMembers()
    {
        return $this->hasMany(ChatMember::class, ['user_id' => 'id']);
    }

    public function getAdministratedGroups()
    {
        return $this->hasMany(Chat::class, ['id' => 'chat_id'])
            ->group()
            ->viaTable(ChatMember::tableName(), ['user_id' => 'id'], function ($query) {
                $query->andWhere([
                    'or',
                    ['status' => ChatMember::STATUS_CREATOR],
                    ['status' => ChatMember::STATUS_ADMINISTRATOR],
                ]);
            })
            ->orderBy(['title' => SORT_ASC]);
    }

    public function getActiveAdministratedGroups()
    {
        return $this->hasMany(Chat::class, ['id' => 'chat_id'])
            ->group()
            ->viaTable(ChatMember::tableName(), ['user_id' => 'id'], function ($query) {
                $query->andWhere([
                    'or',
                    [ChatMember::tableName() . '.status' => ChatMember::STATUS_CREATOR],
                    [ChatMember::tableName() . '.status' => ChatMember::STATUS_ADMINISTRATOR],
                ])
                ->andWhere([
                    ChatMember::tableName() . '.role' => ChatMember::ROLE_ADMINISTRATOR,
                ]);
            })
            ->orderBy(['title' => SORT_ASC]);
    }

    public function getAdministratedChannels()
    {
        return $this->hasMany(Chat::class, ['id' => 'chat_id'])
            ->channel()
            ->viaTable(ChatMember::tableName(), ['user_id' => 'id'], function ($query) {
                $query->andWhere([
                    'or',
                    [ChatMember::tableName() . '.status' => ChatMember::STATUS_CREATOR],
                    [ChatMember::tableName() . '.status' => ChatMember::STATUS_ADMINISTRATOR]
                ]);
            })
            ->orderBy(['title' => SORT_ASC]);
    }

    public function getActiveAdministratedChannels()
    {
        return $this->hasMany(Chat::class, ['id' => 'chat_id'])
            ->channel()
            ->viaTable(ChatMember::tableName(), ['user_id' => 'id'], function ($query) {
                $query->andWhere([
                    'or',
                    [ChatMember::tableName() . '.status' => ChatMember::STATUS_CREATOR],
                    [ChatMember::tableName() . '.status' => ChatMember::STATUS_ADMINISTRATOR]
                ])
                ->andWhere([
                    ChatMember::tableName() . '.role' => ChatMember::ROLE_ADMINISTRATOR,
                ]);
            })
            ->orderBy(['title' => SORT_ASC]);
    }

    /**
     * Gets query for [[Language]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLanguage()
    {
        return $this->hasOne(Language::class, [ 'id' => 'language_id' ]);
    }

    /**
     * Gets query for [[GlobalUser]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGlobalUser()
    {
        return $this->hasOne(GlobalUser::class, ['id' => 'user_id']);
    }

    public function updateInfo($updateUser)
    {
        // check if user changed username
        if ($updateUser->getUsername() != $this->getUsername()) {
            $chats = $this->getGroups()
                ->joinWith('settings')
                ->andWhere([
                    'and',
                    [ChatSetting::tableName() . '.setting' => 'notify_name_change_status'],
                    [ChatSetting::tableName() . '.value' => ChatSetting::STATUS_ON],
                ])
                ->all();

            $module = Yii::$app->getModule('bot');
            foreach ($chats as $group) {
                $module->setChat($group);
                $module->runAction('notify-name-change/username-change', [
                    'group' => $group,
                    'updateUser' => $updateUser,
                    'oldUser' => $this,
                ]);
            }
        }

        if ($updateUser->getFirstName() != $this->provider_user_first_name || $updateUser->getLastName() != $this->provider_user_last_name) {
            $chats = $this->getGroups()
                ->joinWith('settings')
                ->andWhere([
                    'and',
                    [ChatSetting::tableName() . '.setting' => 'notify_name_change_status'],
                    [ChatSetting::tableName() . '.value' => ChatSetting::STATUS_ON],
                ])
                ->all();

            $module = Yii::$app->getModule('bot');
            foreach ($chats as $group) {
                $module->setChat($group);
                $module->runAction('notify-name-change/name-change', [
                    'group' => $group,
                    'updateUser' => $updateUser,
                    'oldUser' => $this,
                ]);
            }
        }

        $this->setAttributes([
            'provider_user_name' => $updateUser->getUsername(),
            'provider_user_first_name' => $updateUser->getFirstName(),
            'provider_user_last_name' => $updateUser->getLastName(),
            'is_bot' => (int)$updateUser->isBot(),
            // TODO only for private chat
            // TODO update if blocked after bot sent a message
            'provider_user_blocked' => 0,
        ]);
    }

    public function getLink()
    {
        return 'tg://user?id=' . $this->provider_user_id;
    }

    public function getFullLink()
    {
        return '<a href="tg://user?id=' . $this->provider_user_id . '">' . $this->getFullName() . '</a>' . ($this->provider_user_name ? ' @' . $this->provider_user_name : '');
    }

    public function getIdFullLink()
    {
        return '<a href="tg://user?id=' . $this->provider_user_id . '">' . $this->provider_user_id . '</a>';
    }

    public static function getFullLinkByProviderUserId(int $providerUserId)
    {
        $telegramUser = self::findOne(['provider_user_id' => $providerUserId]);

        if ($telegramUser) {
            return $telegramUser->getFullLink();
        }

        return false;
    }

    public function getProviderUserId()
    {
        return $this->provider_user_id;
    }

    public function getUsername()
    {
        return $this->provider_user_name;
    }

    /**
     * Gets query for [[UserLocation]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserLocation()
    {
        return $this->hasOne(UserLocation::class, ['user_id' => 'user_id']);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUserId()
    {
        return $this->user_id;
    }

    public function isBot()
    {
        return $this->is_bot;
    }

    /**
     * @return ResponseBuilder
     */
    public function getResponseBuilder()
    {
        return new ResponseBuilder();
    }

    /**
     * @param MessageText $messageText
     * @param array|null $replyMarkup
     * @param array $optionalParams
     *
     * @return
     */
    public function sendMessage(
        MessageText $messageText,
        array $replyMarkup = null,
        array $optionalParams = []
    ) {
        if (!$this->provider_user_blocked && $this->chat) {
            $replyMarkup[] = [
                [
                    'callback_data' => DeleteMessageController::createRoute(),
                    'text' => 'OK',
                ],
            ];

            return $this->getResponseBuilder()
                ->setChatId($this->chat->getChatId())
                ->sendMessage(
                    $messageText,
                    $replyMarkup,
                    $optionalParams
                )
                ->send();
        }

        return false;
    }

    public function useLanguage()
    {
        // Switch to user language
        if (Yii::$app->language != $this->language->code) {
            Yii::$app->language = $this->language->code;
        }
    }

    /**
     * Gets query for [[Contact]].
     *
     * @return ContactQuery
     */
    public function getContacts(): ContactQuery
    {
        return $this->hasMany(Contact::class, ['user_id' => 'user_id'])
            ->joinWith('counterBotUser', $eagerLoading = true, $joinType = 'INNER JOIN');
    }

    /**
     * Gets query for [[Wallet]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWallets()
    {
        return $this->hasMany(Wallet::class, ['user_id' => 'id'])
            ->viaTable(GlobalUser::tableName(), ['id' => 'user_id']);
    }
}
