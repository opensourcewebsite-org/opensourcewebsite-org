<?php

namespace app\modules\bot\models;

use app\models\Language;
use app\models\User as GlobalUser;
use app\models\UserLocation;
use app\modules\bot\components\helpers\MessageText;
use app\modules\bot\components\response\ResponseBuilder;
use app\modules\bot\controllers\privates\DeleteMessageController;
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
            [
                [
                    'provider_user_name',
                    'provider_user_first_name',
                    'provider_user_last_name',
                ],
                'string',
                'max' => 255,
            ],
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
     * @return int|string
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
        if (!empty($this->provider_user_name)) {
            return $this->provider_user_name;
        }

        return '';
    }

    public function getChats()
    {
        return $this->hasMany(Chat::class, ['id' => 'chat_id'])
            ->viaTable('{{%bot_chat_member}}', ['user_id' => 'id']);
    }

    public function getGroups()
    {
        return $this->hasMany(Chat::class, ['id' => 'chat_id'])
            ->where([
                'or',
                ['type' => Chat::TYPE_GROUP],
                ['type' => Chat::TYPE_SUPERGROUP]
            ])
            ->viaTable('{{%bot_chat_member}}', ['user_id' => 'id']);
    }

    // Get private chat
    public function getChat()
    {
        return $this->hasOne(Chat::class, ['id' => 'chat_id'])
            ->where([
                'type' => Chat::TYPE_PRIVATE,
            ])
            ->viaTable('{{%bot_chat_member}}', ['user_id' => 'id']);
    }

    public function getChatMembers()
    {
        return $this->hasMany(ChatMember::class, ['user_id' => 'id']);
    }

    public function getAdministratedGroups()
    {
        return $this->hasMany(Chat::class, ['id' => 'chat_id'])
            ->where([
                'or',
                ['type' => Chat::TYPE_GROUP],
                ['type' => Chat::TYPE_SUPERGROUP],
            ])
            ->viaTable('{{%bot_chat_member}}', ['user_id' => 'id'], function ($query) {
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
            ->where([
                'or',
                ['type' => Chat::TYPE_GROUP],
                ['type' => Chat::TYPE_SUPERGROUP],
            ])
            ->viaTable('{{%bot_chat_member}}', ['user_id' => 'id'], function ($query) {
                $query->andWhere([
                    'or',
                    ['status' => ChatMember::STATUS_CREATOR],
                    ['status' => ChatMember::STATUS_ADMINISTRATOR],
                ])
                ->andWhere([
                    'role' => ChatMember::ROLE_ADMINISTRATOR,
                ]);
            })
            ->orderBy(['title' => SORT_ASC]);
    }

    public function getAdministratedChannels()
    {
        return $this->hasMany(Chat::class, ['id' => 'chat_id'])
            ->where([
                'type' => Chat::TYPE_CHANNEL,
            ])
            ->viaTable('{{%bot_chat_member}}', ['user_id' => 'id'], function ($query) {
                $query->andWhere([
                    'or',
                    ['status' => ChatMember::STATUS_CREATOR],
                    ['status' => ChatMember::STATUS_ADMINISTRATOR]
                ]);
            })
            ->orderBy(['title' => SORT_ASC]);
    }

    public function getActiveAdministratedChannels()
    {
        return $this->hasMany(Chat::class, ['id' => 'chat_id'])
            ->where([
                'type' => Chat::TYPE_CHANNEL,
            ])
            ->viaTable('{{%bot_chat_member}}', ['user_id' => 'id'], function ($query) {
                $query->andWhere([
                    'or',
                    ['status' => ChatMember::STATUS_CREATOR],
                    ['status' => ChatMember::STATUS_ADMINISTRATOR]
                ])
                ->andWhere([
                    'role' => ChatMember::ROLE_ADMINISTRATOR,
                ]);
            })
            ->orderBy(['title' => SORT_ASC]);
    }

    public function getLanguage()
    {
        return $this->hasOne(Language::class, [ 'id' => 'language_id' ]);
    }

    public function getGlobalUser()
    {
        return $this->hasOne(GlobalUser::class, ['id' => 'user_id']);
    }

    public function updateInfo($updateUser)
    {
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

    public function getFullLink()
    {
        return '<a href="tg://user?id=' . $this->provider_user_id . '">' . $this->getFullName() . '</a>' . ($this->provider_user_name ? ' @' . $this->provider_user_name : '');
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
     * @param  array $optionalParams
     *
     * @return
     */
    public function sendMessage(
        MessageText $messageText,
        array $replyMarkup = null,
        array $optionalParams = []
    ) {
        if (!$this->provider_user_blocked && $this->chat) {
            if (!is_array($replyMarkup)) {
                $replyMarkup = [
                    [
                        [
                            'callback_data' => DeleteMessageController::createRoute(),
                            'text' => 'OK',
                        ],
                    ],
                ];
            }

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
}
