<?php
declare(strict_types=1);
namespace app\models\scenarios\Vacancy;

use app\models\Vacancy;
use app\models\JobVacancyKeyword;
use app\components\helpers\ArrayHelper;

class UpdateKeywordsByIdsScenario {

    private Vacancy $model;

    public function __construct(Vacancy $model)
    {
        $this->model = $model;
    }

    public function run()
    {

        $currentKeywordsIds = ArrayHelper::getColumn($this->model->getKeywords()->asArray()->all(), 'id');

        $toDeleteIds = array_diff($currentKeywordsIds, $this->model->keywordsFromForm);
        $toAddIds = array_diff($this->model->keywordsFromForm, $currentKeywordsIds);

        foreach($toAddIds as $id) {
            (new JobVacancyKeyword(['vacancy_id' => $this->model->id, 'job_keyword_id' => $id]))->save();
        }

        $vacancyKeywords = JobVacancyKeyword::find()->where(['vacancy_id' => $this->model->id])->andWhere(['in', 'job_keyword_id', $toDeleteIds])->all();

        /** @var JobVacancyKeyword $vacancyKeyword */
        foreach ($vacancyKeywords as $vacancyKeyword) {
            $vacancyKeyword->delete();
        }
    }
}
