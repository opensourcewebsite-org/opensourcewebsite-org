<?php


namespace app\models\queries;

use app\models\Resume;
use app\models\UserLanguage;
use app\models\Vacancy;
use yii\db\ActiveQuery;
use yii\db\conditions\AndCondition;
use yii\db\conditions\OrCondition;
use yii\db\Expression;

/**
 * Class VacancyQuery
 *
 * @package app\models\queries
 */
class VacancyQuery extends ActiveQuery
{
    /**
     * @return VacancyQuery
     */
    public function active()
    {
        return $this->andWhere([Vacancy::tableName() . '.status' => Vacancy::STATUS_ON])
            ->andWhere(['>=', Vacancy::tableName() . '.renewed_at', time() - Vacancy::LIVE_DAYS * 24 * 60 * 60]);
    }

    /**
     * требуемый уровень языка в вакансии соответствует такому же или большему уровню в резюме.
     * Если в вакансии несколько языков, то они все должны быть быть в резюме
     * (условие AND, языки берутся из профиля пользователя)
     *
     * @return VacancyQuery
     */
    public function matchLanguages()
    {
        $this->joinWith('vacancyLanguagesRelation as lang');
        $this->leftJoin(UserLanguage::tableName(), UserLanguage::tableName() . '.user_id=' . Vacancy::tableName() . '.user_id');
        $this->andWhere(new Expression('(SELECT ' . UserLanguage::tableName()
            . '.language_level_id FROM ' . UserLanguage::tableName() . ' WHERE '
            . UserLanguage::tableName() . '.language_id = lang.language_id LIMIT 1) >= lang.language_level_id'));

        return $this;
    }

    /**
     * совпадения по локации и радиусу поиска,
     * в случае если удаленка выключена в одном из обьектов или в обоих.
     * если в обоих обьектах включена удаленка - то они найдутся
     *
     * @param Resume $model
     *
     * @return $this
     */
    public function matchRadius(Resume $model)
    {
        $radiusExpression = '';
        if ($model->search_radius && $model->location_lat && $model->location_lon) {
            $radiusExpression = new Expression(
                'IF((' . Vacancy::tableName() . '.location_lon AND ' . Vacancy::tableName() . '.location_lat),' .
                'ST_Distance_Sphere(' .
                'POINT(' . $model->location_lon . ', ' . $model->location_lat . '), ' .
                'POINT(' . Vacancy::tableName() . '.location_lon, ' . Vacancy::tableName() . '.location_lat)' .
                '),0) <= 1000 * ' . $model->search_radius
            );
        }
        if ($model->remote_on == Resume::REMOTE_ON) {
            $remoteCondition = [Vacancy::tableName() . '.remote_on' => Vacancy::REMOTE_ON];
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
}
