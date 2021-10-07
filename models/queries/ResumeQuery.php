<?php

declare(strict_types=1);

namespace app\models\queries;

use app\models\queries\builders\ConditionExpressionBuilderInterface;
use app\models\Resume;
use yii\db\ActiveQuery;
use Yii;

/**
 * Class ResumeQuery
 *
 * @package app\models\queries
 */
class ResumeQuery extends ActiveQuery
{
    public function live(): self
    {
        return $this->andWhere([Resume::tableName() . '.status' => Resume::STATUS_ON])
            ->joinWith('user')
            ->andWhere(['>=', 'user.last_activity_at', time() - Resume::LIVE_DAYS * 24 * 60 * 60]);
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
        return $this->$method([Resume::tableName() . '.user_id' => ($id ?? Yii::$app->user->id)]);
    }

    public function excludeUserId(int $id = null, string $method = 'andWhere'): self
    {
        return $this->$method(['!=', Resume::tableName() . '.user_id', ($id ?? Yii::$app->user->id)]);
    }
}
