<?php

declare(strict_types=1);

namespace app\models\queries;

use app\models\AdOffer;
use yii\db\ActiveQuery;
use Yii;

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
            ->andWhere(['>=', 'user.last_activity_at', time() - AdOffer::LIVE_DAYS * 24 * 60 * 60]);
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
        return $this->$method([AdOffer::tableName() . '.user_id' => ($id ?? Yii::$app->user->id)]);
    }

    public function excludeUserId(int $id = null, string $method = 'andWhere'): self
    {
        return $this->$method(['!=', AdOffer::tableName() . '.user_id', ($id ?? Yii::$app->user->id)]);
    }
}
