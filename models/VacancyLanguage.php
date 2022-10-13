<?php

namespace app\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Class VacancyLanguage
 *
 * @package app\models
 *
 * @property int $id
 * @property int $vacancy_id
 * @property int $language_id
 * @property int $language_level_id
 *
 * @property Language $language
 * @property LanguageLevel $level
 * @property string $label
 *
 */
class VacancyLanguage extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%vacancy_language}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['vacancy_id', 'language_id', 'language_level_id'], 'integer'],
            [['vacancy_id', 'language_id', 'language_level_id'], 'required'],
        ];
    }

    public function getVacancy(): ActiveQuery
    {
        return $this->hasOne(Vacancy::className(), ['id' => 'vacancy_id']);
    }

    public function getLevel(): ActiveQuery
    {
        return $this->hasOne(LanguageLevel::class, ['id' => 'language_level_id']);
    }

    public function getLanguage(): ActiveQuery
    {
        return $this->hasOne(Language::class, ['id' => 'language_id']);
    }

    public function getLabel(): string
    {
        return $this->language->name . ' - ' . Yii::t('user', $this->level->description);
    }
}
