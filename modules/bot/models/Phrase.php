<?php
namespace app\modules\bot\models;

use yii\db\ActiveRecord;

class Phrase extends ActiveRecord
{
    public const TYPE_WHITELIST = 'whitelist';
    public const TYPE_BLACKLIST = 'blacklist';

    public static function tableName()
    {
        return 'bot_phrase';
    }

    public function rules()
    {
        return [
            [['chat_id', 'type', 'text', 'created_by'], 'required'],
            [['id', 'chat_id', 'created_by'], 'integer'],
            [['type', 'text'], 'string'],
            [['created_at'], 'default', 'value' => time()],
        ];
    }

    public function isTypeBlack()
    {
        return $this->type == self::TYPE_BLACKLIST;
    }
}
