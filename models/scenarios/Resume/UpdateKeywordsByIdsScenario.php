<?php
declare(strict_types=1);
namespace app\models\scenarios\Resume;

use app\models\Resume;
use app\models\JobResumeKeyword;
use app\components\helpers\ArrayHelper;

class UpdateKeywordsByIdsScenario {

    private Resume $model;

    public function __construct(Resume $model)
    {
        $this->model = $model;
    }

    public function run()
    {

        $currentKeywordsIds = ArrayHelper::getColumn($this->model->getKeywords()->asArray()->all(), 'id');
        $toDeleteIds = array_diff($currentKeywordsIds, $this->model->keywordsFromForm);
        $toAddIds = array_diff($this->model->keywordsFromForm, $currentKeywordsIds);

        foreach($toAddIds as $id) {
            (new JobResumeKeyword(['resume_id' => $this->model->id, 'job_keyword_id' => $id]))->save();
        }

        $resumeKeywords = JobResumeKeyword::find()->where(['resume_id' => $this->model->id])->andWhere(['in', 'job_keyword_id', $toDeleteIds])->all();

        /** @var JobResumeKeyword $resumeKeyword */
        foreach ($resumeKeywords as $resumeKeyword) {
            $resumeKeyword->delete();
        }
    }
}
