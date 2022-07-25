<?php

declare(strict_types=1);

namespace app\repositories;

use app\models\CurrencyExchangeOrder;
use yii\web\NotFoundHttpException;

class CurrencyExchangeOrderRepository
{
    public function findCurrencyExchangeOrderByIdAndCurrentUser(int $id): CurrencyExchangeOrder
    {
        if ($model = CurrencyExchangeOrder::find()
            ->where(['id' => $id])
            ->userOwner()
            ->one()) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
