<?php

declare(strict_types=1);

namespace app\models\queries;

use app\models\AdOffer;
use app\models\User;
use Yii;
use yii\db\ActiveQuery;

/**
 * Class AdOfferQuery
 *
 * @package app\models\queries
 */
class AdOfferQuery extends ActiveQuery
{
    public function live(): self
    {
        return $this->andWhere([AdOffer::tableName() . '.status' => AdOffer::STATUS_ON])
            ->joinWith('user')
            ->andWhere(['>=', User::tableName() . '.last_activity_at', time() - AdOffer::LIVE_DAYS * 24 * 60 * 60]);
    }

    public function orderByRank(): self
    {
        return $this->orderBy([
            User::tableName() . '.rating' => SORT_DESC,
            User::tableName() . '.created_at' => SORT_ASC,
        ]);
    }

    public function userOwner(int $id = null, string $method = 'andWhere'): self
    {
        return $this->$method([AdOffer::tableName() . '.user_id' => ($id ?? Yii::$app->user->id)]);
    }

    public function excludeUserId(int $id = null, string $method = 'andWhere'): self
    {
        return $this->$method(['!=', AdOffer::tableName() . '.user_id', ($id ?? Yii::$app->user->id)]);
    }
}
