<?php
declare(strict_types=1);

namespace app\models\scenarios\AdSearch;

use app\models\AdSearch;
use app\components\helpers\ArrayHelper;
use app\models\AdSearchKeyword;

class UpdateKeywordsByIdsScenario
{
    private AdSearch $model;

    public function __construct(AdSearch $model)
    {
        $this->model = $model;
    }

    public function run()
    {

        $currentKeywordsIds = ArrayHelper::getColumn($this->model->getKeywords()->asArray()->all(), 'id');
        $toDeleteIds = array_diff($currentKeywordsIds, $this->model->keywordsFromForm);
        $toAddIds = array_diff($this->model->keywordsFromForm, $currentKeywordsIds);

        if ($toDeleteIds || $toAddIds) {
            $this->model->trigger(AdSearch::EVENT_KEYWORDS_UPDATED);
        }

        foreach($toAddIds as $id) {
            (new AdSearchKeyword(['ad_search_id' => $this->model->id, 'ad_keyword_id' => $id]))->save();
        }

        $adSearchKeywords = AdSearchKeyword::find()->where(['ad_search_id' => $this->model->id])->andWhere(['in', 'ad_keyword_id', $toDeleteIds])->all();

        /** @var AdSearchKeyword $adSearchKeyword */
        foreach ($adSearchKeywords as $adSearchKeyword) {
            $adSearchKeyword->delete();
        }
    }
}
