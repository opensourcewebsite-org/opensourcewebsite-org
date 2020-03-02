<?php
namespace app\modules\bot\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class Phrase extends ActiveRecord
{
    public const TYPE_WHITE = 'white';
    public const TYPE_BLACK = 'black';

    public static function tableName()
    {
        return 'bot_phrase';
    }

    public function rules()
    {
        return [
            [['group_id', 'type', 'text'], 'required'],
            [['id', 'group_id'], 'integer'],
            [['type', 'text'], 'string'],
        ];
    }

    public function behaviors()
    {
        return [
            // TimestampBehavior::className(),
        ];
    }

    public function isTypeBlack() {
        return $this->type == self::TYPE_BLACK;
    }
}
