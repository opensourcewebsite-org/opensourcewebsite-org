<?php

declare(strict_types=1);

namespace app\models\queries;

use app\models\AdSearch;
use app\models\User;
use Yii;
use yii\db\ActiveQuery;

/**
 * Class AdSearchQuery
 *
 * @package app\models\queries
 */
class AdSearchQuery extends ActiveQuery
{
    /**
     * @return self
     */
    public function live(): self
    {
        return $this->andWhere([AdSearch::tableName() . '.status' => AdSearch::STATUS_ON])
            ->joinWith('user')
            ->andWhere(['>=', User::tableName() . '.last_activity_at', time() - AdSearch::LIVE_DAYS * 24 * 60 * 60]);
    }

    /**
     * @return self
     */
    public function orderByRank(): self
    {
        return $this->joinWith('user')
            ->orderBy([
                User::tableName() . '.rating' => SORT_DESC,
                User::tableName() . '.created_at' => SORT_ASC,
            ]);
    }

    /**
     * @return self
     */
    public function userOwner(int $id = null, string $method = 'andWhere'): self
    {
        return $this->$method([AdSearch::tableName() . '.user_id' => ($id ?? Yii::$app->user->id)]);
    }

    /**
     * @return self
     */
    public function excludeUserId(int $id = null, string $method = 'andWhere'): self
    {
        return $this->$method(['!=', AdSearch::tableName() . '.user_id', ($id ?? Yii::$app->user->id)]);
    }
}
