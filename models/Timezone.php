<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Class Timezone
 * @package app\models
 * @property integer $offset
 * @property string $location
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
            [ [ 'location' ], 'string' ],
            [ [ 'offset' ], 'integer' ],
        ];
    }

    public function getUTCOffset()
    {
        return ($this->offset < 0 ? '-' : '+') . date('H:i', $this->offset);
    }
}