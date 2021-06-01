<?php

namespace app\models\queries;

use app\models\LanguageLevel;
use app\models\queries\builders\ConditionExpressionBuilderInterface;
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
    public function live(): self
    {
        return $this->andWhere([Vacancy::tableName() . '.status' => Vacancy::STATUS_ON])
            ->joinWith('globalUser')
            ->andWhere(['>=', 'user.last_activity_at', time() - Vacancy::LIVE_DAYS * 24 * 60 * 60]);
    }

    public function applyBuilder(ConditionExpressionBuilderInterface $builder): self
    {
        $ret = $builder->build();
        $new = clone $this;
        return $new->andWhere($ret);
    }

}
