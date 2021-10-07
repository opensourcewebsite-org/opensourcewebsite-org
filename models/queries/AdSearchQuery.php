<?php

declare(strict_types=1);

namespace app\models\queries;

use app\models\AdSearch;
use yii\db\ActiveQuery;
use Yii;

/**
 * Class AdSearchQuery
 *
 * @package app\models\queries
 */
class AdSearchQuery extends ActiveQuery
{
    public function live(): self
    {
        return $this->andWhere([AdSearch::tableName() . '.status' => AdSearch::STATUS_ON])
            ->joinWith('user')
            ->andWhere(['>=', 'user.last_activity_at', time() - AdSearch::LIVE_DAYS * 24 * 60 * 60]);
    }

    public function orderByRank(): self
    {
        return $this->orderBy([
            'user.rating' => SORT_DESC,
            'user.created_at' => SORT_ASC,
        ]);
    }

    public function userOwner(int $id = null, string $method = 'andWhere'): self
    {
        return $this->$method([AdSearch::tableName() . '.user_id' => ($id ?? Yii::$app->user->id)]);
    }

    public function excludeUserId(int $id = null, string $method = 'andWhere'): self
    {
        return $this->$method(['!=', AdSearch::tableName() . '.user_id', ($id ?? Yii::$app->user->id)]);
    }
}
