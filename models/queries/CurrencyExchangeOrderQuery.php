<?php

declare(strict_types=1);

namespace app\models\queries;

use app\models\CurrencyExchangeOrder;
use app\models\User;
use Yii;
use yii\db\ActiveQuery;

/**
 * Class CurrencyExchangeOrderQuery
 *
 * @package app\models\queries
 */
class CurrencyExchangeOrderQuery extends ActiveQuery
{
    public function live(): self
    {
        return $this->andWhere([CurrencyExchangeOrder::tableName() . '.status' => CurrencyExchangeOrder::STATUS_ON])
            ->joinWith('user')
            ->andWhere(['>=', User::tableName() . '.last_activity_at', time() - CurrencyExchangeOrder::LIVE_DAYS * 24 * 60 * 60]);
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
        return $this->$method([CurrencyExchangeOrder::tableName() . '.user_id' => ($id ?? Yii::$app->user->id)]);
    }

    public function excludeUserId(int $id = null, string $method = 'andWhere'): self
    {
        return $this->$method(['!=', CurrencyExchangeOrder::tableName() . '.user_id', ($id ?? Yii::$app->user->id)]);
    }
}
