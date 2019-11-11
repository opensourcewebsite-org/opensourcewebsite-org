<?php

namespace app\modules\bot\models;

use app\modules\bot\Module;

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
class BotClient extends \yii\db\ActiveRecord
{
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
                    'provider_user_id',
                ],
                'required',
            ],
            [
                [
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

    /**
     * @return null|BotOutsideMessage
     */
    public function getLastOutsideMessage()
    {
        return BotOutsideMessage::find()
            ->where(['bot_client_id' => $this->id, 'bot_id' => Module::getInstance()->botApi->bot_id])
            ->orderBy('created_at DESC')
            ->one();
    }

    /**
     * @return null|BotInsideMessage
     */
    public function getLastInsideMessage()
    {
        return BotInsideMessage::find()
            ->where(['bot_client_id' => $this->id, 'bot_id' => Module::getInstance()->botApi->bot_id])
            ->orderBy('created_at DESC')
            ->one();
    }
}
