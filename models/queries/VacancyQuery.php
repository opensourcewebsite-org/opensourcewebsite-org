<?php


namespace app\models\queries;

use app\models\Resume;
use app\models\UserLanguage;
use app\models\Vacancy;
use app\models\VacancyLanguage;
use yii\db\ActiveQuery;
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
    public function matchLanguages(Resume $model)
    {
        /** @var UserLanguage[] $userLanguages */
        $userLanguages = $model->userLanguagesRelation;
        $sql = '(SELECT COUNT(*) FROM ' . VacancyLanguage::tableName() . ' `lang` '
            . 'WHERE ' . Vacancy::tableName() . '.`id` = `lang`.`vacancy_id` AND (';
        foreach ($userLanguages as $key => $userLanguage) {
            if ($key) {
                $sql .= ' OR ';
            }
            $sql .= 'lang.language_id = ' . $userLanguage->language_id . ' AND lang.language_level_id <= ' . $userLanguage->language_level_id;
        }
        $sql .= ')) = (SELECT COUNT(*) FROM `vacancy_language` WHERE `vacancy`.`id` = `vacancy_language`.`vacancy_id`)';
        $this->andWhere(new Expression($sql));

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
