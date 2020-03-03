<?php
namespace app\modules\bot\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

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
            [['group_id', 'type', 'text', 'created_by'], 'required'],
            [['id', 'group_id', 'created_by'], 'integer'],
            [['type', 'text'], 'string'],
            [['created_at'], 'default', 'value' => time()],
        ];
    }

    public function isTypeBlack() {
        return $this->type == self::TYPE_BLACK;
    }
}
