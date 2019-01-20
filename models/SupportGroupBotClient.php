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
 * @property string $provider_bot_user_name
 * @property int $provider_bot_user_blocked
 * @property float $location_lat
 * @property float $location_lon
 * @property int $location_at
 *
 * @property SupportGroupBot $supportGroupBot
 * @property SupportGroupClient $supportGroupClient
 */
class SupportGroupBotClient extends \yii\db\ActiveRecord
{
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
            [['support_group_bot_id', 'support_group_client_id', 'provider_bot_user_id', 'provider_bot_user_blocked'], 'required'],
            [['support_group_bot_id', 'support_group_client_id', 'provider_bot_user_id',
                'provider_bot_user_blocked', 'location_at'], 'integer'],
            [['location_lat', 'location_lon'], 'number'],
            [['provider_bot_user_name', 'provider_bot_user_first_name',
                'provider_bot_user_last_name'], 'string', 'max' => 255],
            [['support_group_bot_id'], 'exist', 'skipOnError' => true, 'targetClass' => SupportGroupBot::className(), 'targetAttribute' => ['support_group_bot_id' => 'id']],
            [['support_group_client_id'], 'exist', 'skipOnError' => true, 'targetClass' => SupportGroupClient::className(), 'targetAttribute' => ['support_group_client_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'support_group_bot_id' => 'Support Group Bot ID',
            'support_group_client_id' => 'Support Group Client ID',
            'provider_bot_user_id' => 'Provider Bot User ID',
            'provider_bot_user_name' => 'Provider Bot User Name',
            'provider_bot_user_blocked' => 'Provider Bot User Blocked',
            'provider_bot_user_first_name' => 'Provider Bot User First Name',
            'provider_bot_user_last_name' => 'Provider Bot User Last Name',
            'location_lat' => 'Location Lat',
            'location_lon' => 'Location Lon',
            'location_at' => 'Location At',
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
}
