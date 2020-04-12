<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Class Timezone
 * @package app\models
 * @property integer $offset
 */
class Timezone extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%timezone}}';
    }

    public function rules()
    {
        return [
            [ [ 'offset' ], 'integer' ],
        ];
    }

    /**
     * @return string
     */
    public function getUTCOffset()
    {
        if ($this->offset == 0) {
            return 'UTC';
        }
        return 'UTC ' . ($this->offset < 0 ? '-' : '+') . date('H:i', abs($this->offset));
    }
}
