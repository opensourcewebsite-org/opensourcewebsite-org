<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "timezone".
 *
 * @property integer $code
 * @property string $name
 */
class Timezone extends \yii\db\ActiveRecord
{
    public const TIMEZONE_UTC_CODE = 425;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%timezone}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code', 'name', 'offset'], 'required'],
            [['code', 'offset'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['code'], 'unique'],
        ];
    }

    public function getFullName()
    {
        date_default_timezone_set($this->name);

        return '(UTC ' . date('P', time()) . ') ' . $this->name;
    }
}
