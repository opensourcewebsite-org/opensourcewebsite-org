<?php

declare(strict_types=1);

namespace app\models\scenarios\AdOffer;

use app\models\AdOffer;
use app\models\AdOfferKeyword;
use app\components\helpers\ArrayHelper;

class UpdateKeywordsByIdsScenario
{
    private AdOffer $model;

    public function __construct(AdOffer $model)
    {
        $this->model = $model;
    }

    public function run()
    {
        $currentIds = ArrayHelper::getColumn($this->model->getKeywords()->asArray()->all(), 'id');
        $toDeleteIds = array_diff($currentIds, $this->model->keywordsFromForm);
        $toAddIds = array_diff($this->model->keywordsFromForm, $currentIds);

        if ($toDeleteIds || $toAddIds) {
            $this->model->trigger(AdOffer::EVENT_KEYWORDS_UPDATED);
        }

        foreach ($toAddIds as $id) {
            (new AdOfferKeyword([
                'ad_offer_id' => $this->model->id,
                'ad_keyword_id' => $id,
                ])
            )
            ->save();
        }

        if ($toDeleteIds) {
            AdOfferKeyword::deleteAll([
                'and',
                ['ad_offer_id' => $this->model->id],
                ['in', 'ad_keyword_id', $toDeleteIds],
            ]);
        }
    }
}
