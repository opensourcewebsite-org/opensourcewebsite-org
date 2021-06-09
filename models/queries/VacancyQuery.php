<?php

namespace app\models\queries;

use app\models\queries\builders\ConditionExpressionBuilderInterface;
use app\models\Vacancy;
use yii\db\ActiveQuery;

/**
 * Class VacancyQuery
 *
 * @package app\models\queries
 */
class VacancyQuery extends ActiveQuery
{
    public function live(): self
    {
        return $this->andWhere([Vacancy::tableName() . '.status' => Vacancy::STATUS_ON])
            ->joinWith('user')
            ->andWhere(['>=', 'user.last_activity_at', time() - Vacancy::LIVE_DAYS * 24 * 60 * 60]);
    }

    public function applyBuilder(ConditionExpressionBuilderInterface $builder): self
    {
        $ret = $builder->build();
        $new = clone $this;
        return $new->andWhere($ret);
    }
}
