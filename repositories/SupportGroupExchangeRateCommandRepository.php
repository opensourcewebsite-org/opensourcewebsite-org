<?php

declare(strict_types=1);

namespace app\repositories;

use app\models\SupportGroupExchangeRateCommand;
use yii\web\NotFoundHttpException;

class SupportGroupExchangeRateCommandRepository
{
    public function findSupportGroupExchangeRateCommand(int $id): SupportGroupExchangeRateCommand
    {
        if (($model = SupportGroupExchangeRateCommand::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
