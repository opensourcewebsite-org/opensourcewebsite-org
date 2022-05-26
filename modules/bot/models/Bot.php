<?php

namespace app\modules\bot\models;

use Yii;

/**
 * This is the model class for table "support_group_bot".
 *
 * @property int $id
 * @property string $name
 * @property string $token
 * @property integer $status
 *
 * @property Chat[] $chats
 */
class Bot extends \yii\db\ActiveRecord
{
    public const BOT_STATUS_DISABLED = 0;
    public const BOT_STATUS_ENABLED = 1;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%bot}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'token'], 'required'],
            [['token'], 'validateToken'],
            [['name', 'token'], 'string', 'max' => 255],
            [['status'], 'integer', 'min' => 0, 'max' => 1],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'token' => 'Token',
            'status' => 'Status',
        ];
    }

    /**
     * Validate bot token from telegram API
     *
     * @param $attribute
     * @param $params
     * @param $validator
     */
    public function validateToken($attribute, $params, $validator)
    {
        $botApi = new \TelegramBot\Api\BotApi($this->$attribute);
        if (isset(Yii::$app->params['telegramProxy'])) {
            $botApi->setProxy(Yii::$app->params['telegramProxy']);
        }

        try {
            $botApi->getMe();
        } catch (\TelegramBot\Api\Exception $e) {
            $this->addError($attribute, 'The token is not valid. Error: ' . $e->getMessage());
        }
    }

    /**
     * set webhook for bot token using telegram API
     *
     * @return string
     * @throws \TelegramBot\Api\Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function setWebhook()
    {
        $botApi = new \TelegramBot\Api\BotApi($this->token);
        if (isset(Yii::$app->params['telegramProxy'])) {
            $botApi->setProxy(Yii::$app->params['telegramProxy']);
        }

        $url = Yii::$app->urlManager->createAbsoluteUrl(['/webhook/telegram-bot/' . $this->token]);
        $url = str_replace('http:', 'https:', $url);
        $response = $botApi->setWebhook($url);
        if ($response) {
            $this->status = self::BOT_STATUS_ENABLED;
            $this->update(false, ['status']);
        }

        return $response;
    }

    /**
     * @return mixed
     * @throws \TelegramBot\Api\Exception
     * @throws \TelegramBot\Api\HttpException
     * @throws \TelegramBot\Api\InvalidJsonException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function deleteWebhook()
    {
        $botApi = new \TelegramBot\Api\BotApi($this->token);
        if (isset(Yii::$app->params['telegramProxy'])) {
            $botApi->setProxy(Yii::$app->params['telegramProxy']);
        }
        $response = $botApi->call('deleteWebhook');
        if ($response) {
            $this->status = self::BOT_STATUS_DISABLED;
            $this->update(false, ['status']);
        }

        return $response;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChats()
    {
        return $this->hasMany(Chat::className(), ['bot_id' => 'id']);
    }

    public function getName()
    {
        return $this->name;
    }

    public function getId()
    {
        return $this->id;
    }
}
