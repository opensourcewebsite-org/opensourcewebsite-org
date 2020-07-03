<?php


namespace app\models\queries;

use app\models\UserLanguage;
use app\models\Vacancy;
use yii\db\ActiveQuery;
use yii\db\conditions\AndCondition;
use yii\db\conditions\OrCondition;

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
     * @return $this
     */
    public function languages($userId)
    {
        $userLanguages = UserLanguage::findAll(['user_id' => $userId]);
        $this->joinWith('languagesRelation as lang');
        $conditions = [];
        foreach ($userLanguages as $userLanguage) {
            $conditions[] = new AndCondition([
                ['lang.language_id' => $userLanguage->language_id],
                ['<=', 'lang.language_level_id', $userLanguage->language_level_id],
            ]);
        }
        if ($conditions) {
            $this->andWhere(new OrCondition($conditions));
        }

        return $this;
    }
}
