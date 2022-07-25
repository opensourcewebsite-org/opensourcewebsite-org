<?php

declare(strict_types=1);

namespace app\repositories;

use app\models\Issue;
use Yii;
use yii\web\NotFoundHttpException;

class IssueRepository
{
    public function findIssue(int $id): Issue
    {
        if (($model = Issue::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
