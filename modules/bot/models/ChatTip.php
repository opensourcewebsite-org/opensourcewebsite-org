<?php

declare(strict_types=1);

namespace app\modules\bot\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "bot_chat_tip".
 *
 * @property int $id
 * @property int $chat_id
 * @property int $message_id
 * @property int|null $sent_at
 *
 * @property Chat $chat
 *
 * @package app\modules\bot\models
 */
class ChatTip extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%bot_chat_tip}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['chat_id'], 'required'],
            [['chat_id', 'message_id', 'sent_at'], 'integer'],
            [['chat_id'], 'exist', 'skipOnError' => true, 'targetClass' => Chat::class, 'targetAttribute' => ['chat_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('bot', 'ID'),
            'chat_id' => Yii::t('bot', 'Chat ID'),
            'message_id' => Yii::t('bot', 'Message ID'),
            'sent_at' => Yii::t('bot', 'Sent At'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'sent_at',
                'updatedAtAttribute' => false,
            ],
        ];
    }

    /**
     * Gets query for [[Chat]].
     *
     * @return ActiveQuery
     */
    public function getChat()
    {
        return $this->hasOne(Chat::class, ['id' => 'chat_id']);
    }
}
