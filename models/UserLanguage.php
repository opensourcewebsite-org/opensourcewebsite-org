<?php

namespace app\models;

use yii\db\ActiveRecord;

class UserLanguage extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%user_language}}';
    }

    public function rules()
    {
        return [
            [ [ 'user_id', 'language_id', 'language_level_id' ], 'integer' ],
            [ [ 'user_id', 'language_id', 'language_level_id' ], 'required' ],
        ];
    }

    public function getLevel()
    {
        return $this->hasOne(LanguageLevel::class, [ 'id' => 'language_level_id' ]);
    }

    public function getLanguage()
    {
        return $this->hasOne(Language::class, [ 'id' => 'language_id' ]);
    }

    public function getDisplayName()
    {
        return "{$this->language->name} - {$this->level->description}";
    }
}