<?php

declare(strict_types=1);

namespace app\repositories;

use app\models\Resume;
use app\models\Vacancy;
use app\models\WebModels\WebVacancy;
use yii\web\NotFoundHttpException;

class VacancyRepository
{
    public function findVacancyByIdAndCurrentUser(int $id): WebVacancy
    {
        if (
            $model = WebVacancy::find()
            ->where(['id' => $id])
            ->userOwner()
            ->one()
        ) {
            return $model;
        }

        throw new NotFoundHttpException('Requested Page Not Found');
    }

    public function findMatchedVacancyByIdAndResume(int $id, Resume $resume): Vacancy
    {
        if ($vacancy = $resume->getMatchModels()->where(['id' => $id])->one()) {
            return $vacancy;
        }
        throw new NotFoundHttpException('Requested Page Not Found');
    }
}
