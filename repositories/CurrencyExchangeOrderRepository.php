<?php

declare(strict_types=1);

namespace app\repositories;

use yii\web\NotFoundHttpException;

use app\models\CurrencyExchangeOrder;

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
    