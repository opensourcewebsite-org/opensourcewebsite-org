<?php

namespace app\modules\bot\models;

use yii\db\ActiveRecord;

class Phrase extends ActiveRecord
{
    public const TYPE_WHITELIST = 'whitelist';
    public const TYPE_BLACKLIST = 'blacklist';

    public static function tableName()
    {
        return '{{%bot_phrase}}';
    }

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
}
