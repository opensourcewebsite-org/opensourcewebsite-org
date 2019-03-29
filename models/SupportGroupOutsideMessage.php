<?php

namespace app\models;

use app\models\search\SupportGroupOutsideMessageSearch;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "support_group_outside_message".
 *
 * @property int $id
 * @property int $support_group_bot_id
 * @property int $support_group_bot_client_id
 * @property int $provider_message_id
 * @property string $message
 * @property int $created_at
 * @property int $updated_at
 *
 * @property SupportGroupBotClient $supportGroupBotClient
 * @property SupportGroupBot $supportGroupBot
 */
class SupportGroupOutsideMessage extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'support_group_outside_message';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['support_group_bot_id', 'support_group_bot_client_id', 'message', 'type'], 'required'],
            [['support_group_bot_id', 'support_group_bot_client_id',
                'provider_message_id', 'created_at', 'updated_at', 'type'], 'integer'],
            [['message'], 'string'],
            [['support_group_bot_client_id'], 'exist', 'skipOnError' => true, 'targetClass' => SupportGroupBotClient::className(), 'targetAttribute' => ['support_group_bot_client_id' => 'id']],
            [['support_group_bot_id'], 'exist', 'skipOnError' => true, 'targetClass' => SupportGroupBot::className(), 'targetAttribute' => ['support_group_bot_id' => 'id']],
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
            'support_group_bot_client_id' => 'Support Group Bot Client ID',
            'provider_message_id' => 'Provider Message ID',
            'message' => 'Message',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSupportGroupBotClient()
    {
        return $this->hasOne(SupportGroupBotClient::className(), ['id' => 'support_group_bot_client_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSupportGroupBot()
    {
        return $this->hasOne(SupportGroupBot::className(), ['id' => 'support_group_bot_id']);
    }

    public function getHtmlMessage()
    {
        return "<div>{$this->message}</div>";
    }
    
    public static function getLastPage($id)
    {
        $searchModel = new SupportGroupOutsideMessageSearch();

        $searchModel->support_group_bot_client_id = $id;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return ceil($dataProvider->totalCount/$dataProvider->pagination->pageSize);
    }
}
