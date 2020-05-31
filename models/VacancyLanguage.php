<?php

namespace app\models;

use yii\db\ActiveRecord;
use Yii;

/**
 * Class VacancyLanguage
 * @package app\models
 * @property-read Language $language
 * @property-read LanguageLevel $level
 * @property int $language_id
 * @property int $language_level_id
 * @property int $vacancy_id
 */
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
}
