<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Class Gender
 * @package app\models
 *
 * @property string $name
 */
class Gender extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%gender}}';
    }

    public function rules(): array
    {
        return [
            [ [ 'name' ], 'string' ],
            [ [ 'name' ], 'required' ],
        ];
    }
}
