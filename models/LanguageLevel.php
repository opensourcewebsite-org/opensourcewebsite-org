<?php

namespace app\models;

use yii\db\ActiveRecord;

class LanguageLevel extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%language_level}}';
    }

    public function rules()
    {
        return [
            [ [ 'code', 'description' ], 'string' ],
            [ [ 'value' ], 'integer' ],
            [ [ 'description', 'value' ], 'required' ],
        ];
    }

    public function getDisplayName()
    {
        return (isset($this->code) ? $this->code . ' - ' : '') . $this->description;
    }
}