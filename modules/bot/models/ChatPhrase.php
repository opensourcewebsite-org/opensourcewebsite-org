<?php

declare(strict_types=1);

namespace app\modules\bot\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "bot_chat_phrase".
 *
 * @property int $id
 * @property int $chat_id
 * @property string $type
 * @property string $text
 * @property int $updated_by
 *
 * @package app\modules\bot\models
 */
class ChatPhrase extends ActiveRecord
{
    public const TYPE_WHITELIST = 'whitelist';
    public const TYPE_BLACKLIST = 'blacklist';
    public const TYPE_MARKETPLACE_TAGS = 'marketplace-tags';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%bot_chat_phrase}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['chat_id', 'type', 'text', 'updated_by'], 'required'],
            [['id', 'chat_id', 'updated_by'], 'integer'],
            [['type', 'text'], 'string', 'max' => 255],
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
