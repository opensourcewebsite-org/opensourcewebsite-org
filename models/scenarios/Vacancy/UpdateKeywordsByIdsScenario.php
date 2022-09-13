<?php

declare(strict_types=1);

namespace app\models\scenarios\Vacancy;

use app\components\helpers\ArrayHelper;
use app\models\JobVacancyKeyword;
use app\models\Vacancy;

class UpdateKeywordsByIdsScenario
{
    private Vacancy $model;

    public function __construct(Vacancy $model)
    {
        $this->model = $model;
    }

    public function run()
    {
        $currentIds = ArrayHelper::getColumn($this->model->getKeywords()->asArray()->all(), 'id');

        $toDeleteIds = array_diff($currentIds, $this->model->keywordsFromForm);
        $toAddIds = array_diff($this->model->keywordsFromForm, $currentIds);

        if ($toAddIds || $toDeleteIds) {
            $this->model->trigger(Vacancy::EVENT_KEYWORDS_UPDATED);
        }

        foreach ($toAddIds as $id) {
            (new JobVacancyKeyword([
                'vacancy_id' => $this->model->id,
                'job_keyword_id' => $id,
                ])
            )
            ->save();
        }

        if ($toDeleteIds) {
            JobVacancyKeyword::deleteAll([
                'and',
                ['vacancy_id' => $this->model->id],
                ['in', 'job_keyword_id', $toDeleteIds],
            ]);
        }
    }
}
