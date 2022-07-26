<?php

declare(strict_types=1);

namespace app\repositories;

use app\models\Resume;
use app\models\Vacancy;
use app\models\WebModels\WebResume;
use yii\web\NotFoundHttpException;

class ResumeRepository
{
    public function findMatchedResumeByIdAndVacancy(int $id, Vacancy $vacancy): Resume
    {
        if ($resume = $vacancy->getMatches()->where(['id' => $id])->one()) {
            return $resume;
        }

        throw new NotFoundHttpException('Requested Page Not Found');
    }

    public function findResumeByIdAndCurrentUser(int $id): Resume
    {
        if (
            $model = Resume::find()
            ->where(['id' => $id])
            ->userOwner()
            ->one()
        ) {
            return $model;
        }

        throw new NotFoundHttpException('Requested Page Not Found');
    }
}
