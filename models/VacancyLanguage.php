<?php

namespace app\models;

use yii\db\ActiveRecord;
use Yii;

class VacancyLanguage extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%vacancy_language}}';
    }

    public function rules()
    {
        return [
            [ [ 'vacancy_id', 'language_id', 'language_level_id' ], 'integer' ],
            [ [ 'vacancy_id', 'language_id', 'language_level_id' ], 'required' ],
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
        return $this->language->name . ' - ' . Yii::t('app', $this->level->description);
    }
}
