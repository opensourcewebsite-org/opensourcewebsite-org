<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "support_group_bot".
 *
 * @property int $id
 * @property int $support_group_id
 * @property string $title
 * @property string $token
 * @property int $updated_at
 * @property int $updated_by
 *
 * @property SupportGroup $supportGroup
 * @property SupportGroupClientBot[] $supportGroupClientBots
 * @property SupportGroupInsideMessage[] $supportGroupInsideMessages
 * @property SupportGroupOutsideMessage[] $supportGroupOutsideMessages
 */
class SupportGroupBot extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'support_group_bot';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => false,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['support_group_id', 'title', 'token'], 'required'],
            [['support_group_id'], 'integer'],
            [['token'], 'validateToken'],
            [['title'], 'validateMaxCount'],
            [['title', 'token'], 'string', 'max' => 255],
            [['support_group_id'], 'exist', 'skipOnError' => true, 'targetClass' => SupportGroup::className(), 'targetAttribute' => ['support_group_id' => 'id']],
        ];
    }

    /**
     * Validates the max allowed bot count reached.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validateMaxCount($attribute, $params)
    {
        if (!$this->hasErrors()) {
            if (Yii::$app->user->identity->botsCount >= Yii::$app->user->identity->maxBots) {
                $this->addError($attribute, 'You are not allowed to add more bots.');
            }
        }
    }

    /**
     * Validate bot token from telegram API
     */
    public function validateToken($attribute, $params, $validator)
    {
        $botApi = new \TelegramBot\Api\BotApi($this->$attribute);
        if (isset(Yii::$app->params['telegramProxy'])) {
            $botApi->setProxy(Yii::$app->params['telegramProxy']);
        }

        try {
            $botUser = $botApi->getMe();
        } catch (\TelegramBot\Api\Exception $e) {
            $this->addError($attribute, 'The token is not valid. Error: ' . $e->getMessage());
        }
    }

    /**
     * set webhook for bot token using telegram API
     */
    public function setWebhook()
    {
        $botApi = new \TelegramBot\Api\BotApi($this->token);
        if (isset(Yii::$app->params['telegramProxy'])) {
            $botApi->setProxy(Yii::$app->params['telegramProxy']);
        }

        $url = Yii::$app->urlManager->createAbsoluteUrl(['/webhook/telegram/' . $this->token]);
        $url = str_replace('http:', 'https:', $url);
        return $botApi->setWebhook($url);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'support_group_id' => 'Support Group ID',
            'title' => 'Title',
            'token' => 'Token',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSupportGroup()
    {
        return $this->hasOne(SupportGroup::className(), ['id' => 'support_group_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSupportGroupClientBots()
    {
        return $this->hasMany(SupportGroupClientBot::className(), ['support_group_bot_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSupportGroupInsideMessages()
    {
        return $this->hasMany(SupportGroupInsideMessage::className(), ['support_group_bot_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSupportGroupOutsideMessages()
    {
        return $this->hasMany(SupportGroupOutsideMessage::className(), ['support_group_bot_id' => 'id']);
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $this->updated_by = Yii::$app->user->id;

            return true;
        }
        return false;
    }
}
