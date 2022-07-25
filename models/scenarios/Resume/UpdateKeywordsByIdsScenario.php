<?php

declare(strict_types=1);

namespace app\models\scenarios\Resume;

use app\models\Resume;
use app\models\JobResumeKeyword;
use app\components\helpers\ArrayHelper;

class UpdateKeywordsByIdsScenario
{
    private Resume $model;

    public function __construct(Resume $model)
    {
        $this->model = $model;
    }

    public function run()
    {
        $currentIds = ArrayHelper::getColumn($this->model->getKeywords()->asArray()->all(), 'id');
        $toDeleteIds = array_diff($currentIds, $this->model->keywordsFromForm);
        $toAddIds = array_diff($this->model->keywordsFromForm, $currentIds);

        if ($toDeleteIds || $toAddIds) {
            $this->model->trigger(Resume::EVENT_KEYWORDS_UPDATED);
        }

        foreach ($toAddIds as $id) {
            (new JobResumeKeyword([
                'resume_id' => $this->model->id,
                'job_keyword_id' => $id,
                ])
            )
            ->save();
        }

        if ($toDeleteIds) {
            JobResumeKeyword::deleteAll([
                'and',
                ['resume_id' => $this->model->id],
                ['in', 'job_keyword_id', $toDeleteIds],
            ]);
        }
    }
}
