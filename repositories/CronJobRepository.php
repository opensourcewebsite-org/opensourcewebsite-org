<?php

declare(strict_types=1);

namespace app\repositories;

use Yii;
use yii\web\NotFoundHttpException;

use app\models\CronJob;

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
