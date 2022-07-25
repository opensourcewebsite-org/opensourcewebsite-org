<?php

declare(strict_types=1);

namespace app\repositories;

use app\models\SupportGroupExchangeRate;
use yii\web\NotFoundHttpException;

class SupportGroupExchangeRateRepository
{
    public function findSupportGroupExchangeRate(int $id): SupportGroupExchangeRate
    {
        if (($model = SupportGroupExchangeRate::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
