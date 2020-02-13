<?php

namespace app\modules\bot\models;

use \yii\db\ActiveRecord;

/**
 * This is the model class for table "support_group_bot_client".
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
    private $_stateObject;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'bot_client';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [
                [
                    'bot_id',
                    'provider_user_id',
                ],
                'required',
            ],
            [
                [
                    'bot_id',
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
    public function showUserName()
    {
        if (!empty($this->provider_user_first_name) || !empty($this->provider_user_last_name)) {
            return trim($this->provider_user_first_name . ' ' . $this->provider_user_last_name);
        } elseif (!empty($this->provider_user_name)) {
            return $this->provider_user_name;
        } else {
            return $this->provider_user_first_name;
        }
    }

    public function getState()
    {
        if (!isset($this->_stateObject)) {
            $this->_stateObject = isset($this->state)
               ? BotClientState::fromJson($this->state)
               : new BotClientState();
        }
        return $this->_stateObject;
    }

    public function save($runValidation = true, $attributeNames = null)
    {
        $this->state = $this->getState()->toJson();
        return parent::save($runValidation, $attributeNames);
    }
}
