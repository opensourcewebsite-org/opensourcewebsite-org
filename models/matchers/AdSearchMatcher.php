<?php

declare(strict_types=1);

namespace app\models\matchers;

use app\models\AdOffer;
use app\models\AdOfferKeyword;
use app\models\AdSearch;
use yii\db\ActiveQuery;

class AdSearchMatcher
{
    private AdSearch $model;
    private ModelLinker $linker;
    private string $comparingTable;

    public function __construct(AdSearch $model)
    {
        $this->model = $model;
        $this->linker = new ModelLinker($this->model);
        $this->comparingTable = AdOffer::tableName();
    }

    public function match(): int
    {
        $this->linker->unlinkMatches();
        $matchesQuery = $this->prepareMainQuery();

        $matchesQueryNoKeywords = clone $matchesQuery;

        $matchesQueryNoKeywords = $matchesQueryNoKeywords
            ->andWhere(['not in', $this->comparingTable . '.id', AdOfferKeyword::find()->select('ad_offer_id')]);

        $matchesQueryKeywords = clone $matchesQuery;

        $matchesQueryKeywords = $matchesQueryKeywords
            ->joinWith(
                [
                    'keywords' => function ($query) {
                        $query
                            ->joinWith('adSearches')
                            ->andWhere([AdSearch::tableName() . '.id' => $this->model->id]);
                    },
                ]
            )
            ->groupBy(AdOffer::tableName() . '.id');

        if ($this->model->getKeywords()->count() > 0) {
            $keywordsMatches = $matchesQueryKeywords->all();
            $noKeywordsMatches = $matchesQueryNoKeywords->all();

            $matchedCount = count($keywordsMatches);

            $this->linker->linkMatches($keywordsMatches);
            $this->linker->linkCounterMatches($keywordsMatches);
            $this->linker->linkMatches($noKeywordsMatches);
        } else {
            $keywordsMatches = $matchesQueryKeywords->all();
            $noKeywordsMatches = $matchesQueryNoKeywords->all();

            $matchedCount = count($noKeywordsMatches);

            $this->linker->linkCounterMatches($keywordsMatches);
            $this->linker->linkMatches($noKeywordsMatches);
            $this->linker->linkCounterMatches($noKeywordsMatches);
        }

        return $matchedCount;
    }

    private function prepareMainQuery(): ActiveQuery
    {
        return AdOffer::find()
            ->excludeUserId($this->model->user_id)
            ->live()
            ->andWhere([$this->comparingTable . '.section' => $this->model->section])
            ->andWhere(
                "ST_Distance_Sphere(
                    POINT({$this->model->location_lon}, {$this->model->location_lat}),
                    POINT(ad_offer.location_lon, ad_offer.location_lat)
                ) <= 1000 * (ad_offer.delivery_radius + {$this->model->pickup_radius})"
            );
    }
}
