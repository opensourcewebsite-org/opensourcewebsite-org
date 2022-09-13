<?php

declare(strict_types=1);

namespace app\models\queries;

use app\models\queries\builders\ConditionExpressionBuilderInterface;
use app\models\User;
use app\models\Vacancy;
use Yii;
use yii\db\ActiveQuery;

/**
 * Class VacancyQuery
 *
 * @package app\models\queries
 */
class VacancyQuery extends ActiveQuery
{
    /**
     * @return self
     */
    public function live(): self
    {
        return $this->andWhere([Vacancy::tableName() . '.status' => Vacancy::STATUS_ON])
            ->joinWith('user')
            ->andWhere(['>=', User::tableName() . '.last_activity_at', time() - Vacancy::LIVE_DAYS * 24 * 60 * 60]);
    }

    /**
     * @return self
     */
    public function orderByRank(): self
    {
        return $this->orderBy([
            User::tableName() . '.rating' => SORT_DESC,
            User::tableName() . '.created_at' => SORT_ASC,
        ]);
    }

    /**
     * @return self
     */
    public function applyBuilder(ConditionExpressionBuilderInterface $builder): self
    {
        $ret = $builder->build();
        $new = clone $this;

        return $new->andWhere($ret);
    }

    /**
     * @return self
     */
    public function userOwner(int $id = null, string $method = 'andWhere'): self
    {
        return $this->$method([Vacancy::tableName() . '.user_id' => ($id ?? Yii::$app->user->id)]);
    }

    /**
     * @return self
     */
    public function excludeUserId(int $id = null, string $method = 'andWhere'): self
    {
        return $this->$method(['!=', Vacancy::tableName() . '.user_id', ($id ?? Yii::$app->user->id)]);
    }
}
