<?php

declare(strict_types=1);

namespace app\repositories;

use yii\web\NotFoundHttpException;

use app\models\SupportGroupExchangeRateCommand;

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
    