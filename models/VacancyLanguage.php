<?php


namespace app\models;

use Yii;
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
 */
class VacancyLanguage extends ActiveRecord
{
    /** @inheritDoc */
    public static function tableName()
    {
        return '{{%vacancy_language}}';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLevelRelation()
    {
        return $this->hasOne(LanguageLevel::class, ['id' => 'language_level_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLanguageRelation()
    {
        return $this->hasOne(Language::class, ['id' => 'language_id']);
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->languageRelation->name . ' - ' . Yii::t('app', $this->levelRelation->description);
    }
}
