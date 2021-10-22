<?php

namespace app\modules\bot\models;

use Yii;
use yii\db\ActiveRecord;
use app\models\Language;
use app\models\User as GlobalUser;

/**
 * This is the model class for table "bot_user".
 *
 * @property int $id
 * @property int $provider_user_id
 * @property string $provider_user_name
 * @property bool $provider_user_blocked
 * @property string $provider_user_first_name
 * @property string $provider_user_last_name
 * @property string $location_lat
 * @property string $location_lon
 * @property int $location_at
 * @property string $language_code
 * @property int $user_id
 * @property string $currency_code
 * @property string $state
 * @property int $language_id
 * @property bool $is_bot
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
            [
                [
                    'provider_user_id',
                ],
                'required',
            ],
            [
                [
                    'user_id',
                    'provider_user_id',
                    'provider_user_blocked',
                    'location_at',
                    'language_id',
                    'is_bot',
                ],
                'integer',
            ],
            [
                [
                    'location_lat',
                    'location_lon',
                ],
                'number'
            ],
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
            'location_lat' => 'Location Lat',
            'location_lon' => 'Location Lon',
            'location_at' => 'Location',
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

    public function getLocation(): string
    {
        return ($this->location_lat && $this->location_lon) ?
            implode(',', [$this->location_lat, $this->location_lon]) :
            '';
    }
}
