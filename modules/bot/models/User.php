<?php

namespace app\modules\bot\models;

use \yii\db\ActiveRecord;
use app\models\Language;

/**
 * This is the model class for table "bot_user".
 *
 * @property int $id
 * @property int $provider_user_id
 * @property string $provider_user_name
 * @property string $provider_user_first_name
 * @property string $provider_user_last_name
 * @property int $provider_user_blocked
 * @property string $location_lat
 * @property string $location_lon
 * @property int $location_at
 * @property int $last_message_at
 * @property string $language_code
 * @property string $currency_code
 */
class User extends ActiveRecord
{
    private $stateObject;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'bot_user';
    }

    public static function createUser($updateUser)
    {
        $language = Language::findOne([
            'code' => $updateUser->getLanguageCode(),
        ]);
        $languageCode = isset($language) ? $language->code : 'en';

        $newUser = new User();
        $newUser->setAttributes([
            'provider_user_id' => $updateUser->getId(),
            'language_code' => $languageCode,
            'is_authenticated' => true,
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
                    'last_message_at',
                ],
                'integer',
            ],
            [['location_lat', 'location_lon'], 'number'],
            [
                [
                    'provider_user_name',
                    'provider_user_first_name',
                    'provider_user_last_name',
                    'language_code',
                    'currency_code',
                ],
                'string',
                'max' => 255,
            ],
            [['language_code'], 'default', 'value' => 'en'],
            [['currency_code'], 'default', 'value' => 'USD'],
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
            'last_message_at' => 'Last activity',
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
        if (!empty($this->provider_user_first_name) || !empty($this->provider_user_last_name)) {
            return trim($this->provider_user_first_name) . ' ' . trim($this->provider_user_last_name);
        } elseif (!empty($this->provider_user_first_name)) {
            return trim($this->provider_user_first_name);
        } elseif (!empty($this->provider_user_last_name)) {
            return trim($this->provider_user_last_name);
        } elseif (!empty($this->provider_user_name)) {
            return trim($this->provider_user_name);
        }
    }

    public function getChats()
    {
        return $this->hasMany(Chat::className(), ['id' => 'chat_id'])
            ->viaTable('{{%bot_chat_member}}', ['user_id' => 'id']);
    }

    public function getAdministratedChats()
    {
        return $this->hasMany(Chat::className(), ['id' => 'chat_id'])
            ->viaTable('{{%bot_chat_member}}', ['user_id' => 'id'], function ($query) {
                $query->andWhere([
                    'or',
                    ['status' => ChatMember::STATUS_CREATOR],
                    ['status' => ChatMember::STATUS_ADMINISTRATOR]
                ]);
        });
    }

    public function updateInfo($updateUser)
    {
        $this->setAttributes([
            'provider_user_name' => $updateUser->getUsername(),
            'provider_user_first_name' => $updateUser->getFirstName(),
            'provider_user_last_name' => $updateUser->getLastName(),
            'provider_bot_user_blocked' => 0,
            'last_message_at' => time(),
        ]);

        $this->save();
    }
}
