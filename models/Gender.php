<?php

namespace app\models;

use app\models\queries\GenderQuery;
use yii\db\ActiveRecord;

/**
 * Class Gender
 * @package app\models
 *
 * @property string $name
 * @property int $id
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

    public static function find(): GenderQuery
    {
        return new GenderQuery(get_called_class());
    }
}
