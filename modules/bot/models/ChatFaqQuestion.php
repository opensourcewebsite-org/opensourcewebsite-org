<?php

declare(strict_types=1);

namespace app\modules\bot\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "bot_chat_faq_question".
 *
 * @property int $id
 * @property int $chat_id
 * @property string $text
 * @property string $answer
 * @property int $updated_by
 *
 * @property Chat $chat
 * @property User $updatedBy
 *
 * @package app\modules\bot\models
 */
class ChatFaqQuestion extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%bot_chat_faq_question}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['chat_id', 'text', 'updated_by'], 'required'],
            [['chat_id', 'updated_by'], 'integer'],
            [['text'], 'string', 'max' => 255],
            [['answer'], 'string', 'max' => 10000],
            [['chat_id'], 'exist', 'skipOnError' => true, 'targetClass' => Chat::class, 'targetAttribute' => ['chat_id' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['updated_by' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'chat_id' => 'Chat ID',
            'text' => 'Text',
            'answer' => 'Answer',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[Chat]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getChat()
    {
        return $this->hasOne(Chat::className(), ['id' => 'chat_id']);
    }

    /**
     * Gets query for [[UpdatedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }

    public function getChatId()
    {
        return $this->chat_id;
    }
}
