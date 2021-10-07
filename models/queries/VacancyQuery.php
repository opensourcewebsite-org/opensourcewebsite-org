<?php

declare(strict_types=1);

namespace app\models\queries;

use app\models\queries\builders\ConditionExpressionBuilderInterface;
use app\models\Vacancy;
use yii\db\ActiveQuery;
use Yii;

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

    public function orderByRank(): self
    {
        return $this->orderBy([
            'user.rating' => SORT_DESC,
            'user.created_at' => SORT_ASC,
        ]);
    }

    public function applyBuilder(ConditionExpressionBuilderInterface $builder): self
    {
        $ret = $builder->build();
        $new = clone $this;

        return $new->andWhere($ret);
    }

    public function userOwner(int $id = null, string $method = 'andWhere'): self
    {
        return $this->$method([Vacancy::tableName() . '.user_id' => ($id ?? Yii::$app->user->id)]);
    }

    public function excludeUserId(int $id = null, string $method = 'andWhere'): self
    {
        return $this->$method(['!=', Vacancy::tableName() . '.user_id', ($id ?? Yii::$app->user->id)]);
    }
}
