<?php

namespace app\models\queries;

use app\models\Language;
use app\models\LanguageLevel;
use app\models\Resume;
use app\models\UserLanguage;
use app\models\Vacancy;
use app\models\VacancyLanguage;
use yii\db\ActiveQuery;
use yii\db\conditions\OrCondition;
use yii\db\Expression;

/**
 * Class ResumeQuery
 *
 * @package app\models\queries
 */
class ResumeQuery extends ActiveQuery
{
    /**
     * @return ResumeQuery
     */
    public function live()
    {
        return $this->andWhere([Resume::tableName() . '.status' => Resume::STATUS_ON])
            ->joinWith('globalUser')
            ->andWhere(['>=', 'user.last_activity_at', time() - Resume::LIVE_DAYS * 24 * 60 * 60]);
    }

    /**
     * совпадения по локации и радиусу поиска,
     * в случае если удаленка выключена в одном из обьектов или в обоих.
     * если в обоих обьектах включена удаленка - то они найдутся
     *
     * @param Vacancy $model
     *
     * @return $this
     */
    public function matchRadius(Vacancy $model)
    {
        $radiusExpression = '';
        if ($model->location_lat && $model->location_lon) {
            $radiusExpression = new Expression(
                'IF((' . Resume::tableName() . '.location_lon AND ' . Resume::tableName() . '.location_lat AND ' . Resume::tableName() . '.search_radius > 0),' .
                'ST_Distance_Sphere(' .
                'POINT(' . $model->location_lon . ', ' . $model->location_lat . '), ' .
                'POINT(' . Resume::tableName() . '.location_lon, ' . Resume::tableName() . '.location_lat)' .
                '),0) <= (1000 * ' . Resume::tableName() . '.search_radius)'
            );
        }
        if ($model->remote_on == Vacancy::REMOTE_ON) {
            $remoteCondition = [Resume::tableName() . '.remote_on' => Resume::REMOTE_ON];
            if ($radiusExpression) {
                $this->andWhere(new OrCondition([$remoteCondition, $radiusExpression]));
            } else {
                $this->andWhere($remoteCondition);
            }
        } elseif ($radiusExpression) {
            $this->andWhere($radiusExpression);
        }

        return $this;
    }

    /**
     * требуемый уровень языка в вакансии соответствует такому же или большему уровню в резюме.
     * Если в вакансии несколько языков, то они все должны быть в резюме
     * (условие AND, языки берутся из профиля пользователя)
     *
     * @return ResumeQuery
     */
    public function matchLanguages(Vacancy $model)
    {
        /** @var VacancyLanguage[] $vacancyLanguages */
        $vacancyLanguages = $model->vacancyLanguagesRelation;
        if ($vacancyLanguages) {
            $sql = '(SELECT COUNT(*) FROM ' . UserLanguage::tableName() . ' `lang` '
                . 'INNER JOIN ' . LanguageLevel::tableName() . ' ON lang.language_level_id = ' . LanguageLevel::tableName() . '.id '
                . 'WHERE (';
            foreach ($vacancyLanguages as $key => $vacancyLanguage) {
                $languageLevel = $vacancyLanguage->levelRelation;
                if ($key) {
                    $sql .= ' OR ';
                }
                $sql .= 'lang.language_id = ' . $vacancyLanguage->language_id . ' AND ' . LanguageLevel::tableName() . '.value >= ' . $languageLevel->value;
            }
            $sql .= ')) = ' . count($vacancyLanguages);
            $this->andWhere(new Expression($sql));
        }

        return $this;
    }
}
