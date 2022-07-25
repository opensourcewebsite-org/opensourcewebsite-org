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
        $currentIds = ArrayHelper::getColumn($this->model->getKeywords()->asArray()->all(), 'id');
        $toDeleteIds = array_diff($currentIds, $this->model->keywordsFromForm);
        $toAddIds = array_diff($this->model->keywordsFromForm, $currentIds);

        if ($toDeleteIds || $toAddIds) {
            $this->model->trigger(AdSearch::EVENT_KEYWORDS_UPDATED);
        }

        foreach ($toAddIds as $id) {
            (new AdSearchKeyword([
                'ad_search_id' => $this->model->id,
                'ad_keyword_id' => $id,
                ])
            )
            ->save();
        }

        if ($toDeleteIds) {
            AdSearchKeyword::deleteAll([
                'and',
                ['ad_search_id' => $this->model->id],
                ['in', 'ad_keyword_id', $toDeleteIds],
            ]);
        }
    }
}
