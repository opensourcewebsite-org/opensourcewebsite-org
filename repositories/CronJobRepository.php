<?php

declare(strict_types=1);

namespace app\repositories;

use app\models\CronJob;
use Yii;
use yii\web\NotFoundHttpException;

class CronJobRepository
{
    public function findAllCronJob()
    {
        if (($model = CronJob::find()->all()) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
