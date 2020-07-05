<?php


namespace app\models\queries;

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
     * @return VacancyQuery
     */
    public function languages()
    {
        $this->joinWith('vacancyLanguagesRelation as lang');
        $this->leftJoin(UserLanguage::tableName(), UserLanguage::tableName() . '.user_id=' . Vacancy::tableName() . '.user_id');
        $this->andWhere(new Expression('(SELECT ' . UserLanguage::tableName()
            . '.language_level_id FROM ' . UserLanguage::tableName() . ' WHERE '
            . UserLanguage::tableName() . '.language_id = lang.language_id) >= lang.language_level_id'));

        return $this;
    }
}
