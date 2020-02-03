<?php

namespace app\modules\bot\models;

use app\modules\bot\telegram\BotApiClient;
use phpDocumentor\Reflection\Types\Self_;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "support_group_outside_message".
 *
 * @property int $id
 * @property int $bot_id
 * @property int $client_id
 * @property int $provider_message_id
 * @property int $provider_chat_id
 * @property string $message
 * @property int $type
 * @property int $created_at
 * @property int $updated_at
 *
 * @property BotClient $botClient
 * @property Bot $bot
 */
class BotOutsideMessage extends \yii\db\ActiveRecord
{
    const TYPE_ORDINARY_TEXT = 1;
    const TYPE_COMMAND = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'bot_outside_message';
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
            [['bot_id', 'bot_client_id', 'message', 'type'], 'required'],
            [['bot_id', 'bot_client_id', 'provider_message_id', 'provider_chat_id', 'created_at', 'updated_at', 'type'], 'integer'],
            [['message'], 'string'],
            [
                ['bot_client_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => BotClient::className(),
                'targetAttribute' => ['bot_client_id' => 'id'],
            ],
            [
                ['bot_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Bot::className(),
                'targetAttribute' => ['bot_id' => 'id'],
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
            'bot_id' => 'Bot ID',
            'bot_client_id' => 'Bot Client ID',
            'provider_message_id' => 'Provider Message ID',
            'provider_chat_id' => 'Provider Chat ID',
            'message' => 'Message',
            'type' => 'Type',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBotClient()
    {
        return $this->hasOne(BotClient::className(), ['id' => 'bot_client_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBot()
    {
        return $this->hasOne(Bot::className(), ['id' => 'bot_id']);
    }

    /**
     * @param $botApi BotApiClient
     *
     * @return bool
     */
    public static function saveMessage($botApi)
    {
        if (!$botApi->getMessage()) {
            return false;
        }

        $text = BotApiClient::cleanEmoji(trim($botApi->getMessage()->getText()));
        $chatId = \Yii::$app->requestMessage->getChat()->getId();

        if (mb_strlen($text) == 0) {
            return false;
        }

        $model = new self();
        $model->setAttributes([
            'bot_id' => $botApi->bot_id,
            'bot_client_id' => $botApi->bot_client_id,
            'type' => $botApi->type,
            'provider_message_id' => $botApi->getMessage()->getMessageId(),
            'provider_chat_id' => $botApi->getMessage()->getChat()->getId(),
            'message' => $text,
        ]);

        return $model->save();
    }
}
