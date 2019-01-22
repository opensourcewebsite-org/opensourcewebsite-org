<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "support_group_bot_client".
 *
 * @property int $id
 * @property int $support_group_bot_id
 * @property int $support_group_client_id
 * @property int $provider_bot_user_id
 * @property string $description
 * @property string $provider_bot_user_name
 * @property string $provider_bot_user_first_name
 * @property string $provider_bot_user_last_name
 * @property int $provider_bot_user_blocked
 * @property float $location_lat
 * @property float $location_lon
 * @property int $location_at
 * @property int $last_message_at
 *
 * @property SupportGroupBot $supportGroupBot
 * @property SupportGroupClient $supportGroupClient
 */
class SupportGroupBotClient extends \yii\db\ActiveRecord
{
    const LIMIT_MESSAGES = 200;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'support_group_bot_client';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [
                [
                    'support_group_bot_id', 'support_group_client_id', 'provider_bot_user_id',
                    'provider_bot_user_blocked',
                ], 'required',
            ],
            [
                [
                    'support_group_bot_id', 'support_group_client_id', 'provider_bot_user_id',
                    'provider_bot_user_blocked', 'location_at', 'last_message_at',
                ], 'integer',
            ],
            [['location_lat', 'location_lon'], 'number'],
            [
                [
                    'provider_bot_user_name', 'provider_bot_user_first_name',
                    'provider_bot_user_last_name',
                ], 'string', 'max' => 255,
            ],
            [['description'], 'string'],
            [
                ['support_group_bot_id'], 'exist', 'skipOnError'     => true,
                                                   'targetClass'     => SupportGroupBot::className(),
                                                   'targetAttribute' => ['support_group_bot_id' => 'id'],
            ],
            [
                ['support_group_client_id'], 'exist', 'skipOnError'     => true,
                                                      'targetClass'     => SupportGroupClient::className(),
                                                      'targetAttribute' => ['support_group_client_id' => 'id'],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'                           => 'ID',
            'support_group_bot_id'         => 'Support Group Bot ID',
            'support_group_client_id'      => 'Support Group Client ID',
            'provider_bot_user_id'         => 'Provider Bot User ID',
            'provider_bot_user_name'       => 'Username',
            'provider_bot_user_blocked'    => 'Provider Bot User Blocked',
            'provider_bot_user_first_name' => 'First name',
            'provider_bot_user_last_name'  => 'Last name',
            'last_message_at'              => 'Last activity',
            'location_lat'                 => 'Location Lat',
            'location_lon'                 => 'Location Lon',
            'location_at'                  => 'Location',
            'description'                  => 'Description',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSupportGroupBot()
    {
        return $this->hasOne(SupportGroupBot::className(), ['id' => 'support_group_bot_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSupportGroupClient()
    {
        return $this->hasOne(SupportGroupClient::className(), ['id' => 'support_group_client_id']);
    }

    /**
     * @return int|string
     */
    public function showUserName()
    {
        if (!empty($this->provider_bot_user_first_name) || !empty($this->provider_bot_user_last_name)) {
            return trim($this->provider_bot_user_first_name . ' ' . $this->provider_bot_user_last_name);
        } elseif (!empty($this->provider_bot_user_name)) {
            return $this->provider_bot_user_name;
        } else {
            return $this->provider_bot_user_id;
        }
    }
}
