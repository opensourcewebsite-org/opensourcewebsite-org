<?php

declare(strict_types=1);

namespace app\models\matchers;

use app\models\AdOffer;
use app\models\AdSearch;
use app\models\AdSearchKeyword;
use yii\db\ActiveQuery;

class AdOfferMatcher
{
    private AdOffer $model;
    private ModelLinker $linker;
    private string $comparingTable;

    public function __construct(AdOffer $model)
    {
        $this->model = $model;
        $this->linker = new ModelLinker($this->model);
        $this->comparingTable = AdSearch::tableName();
    }

    public function match(): int
    {
        $this->linker->unlinkMatches();
        $matchesQuery = $this->prepareMainQuery();

        $matchesQueryNoKeywords = clone $matchesQuery;
        $matchesQueryNoKeywords = $matchesQueryNoKeywords
            ->andWhere(['not in', "{$this->comparingTable}.id", AdSearchKeyword::find()->select('ad_search_id')]);

        $matchesQueryKeywords = clone $matchesQuery;
        $matchesQueryKeywords = $matchesQueryKeywords
            ->joinWith(['keywords' => function ($query) {
                $query
                    ->joinWith('adOffers')
                    ->andWhere([AdOffer::tableName() . '.id' => $this->model->id]);
            }])
            ->groupBy($this->comparingTable . '.id');

        if ($this->model->getKeywords()->count() > 0) {
            $keywordsMatches = $matchesQueryKeywords->all();
            $noKeywordsMatches = $matchesQueryNoKeywords->all();

            $matchesCount = count($keywordsMatches);

            $this->linker->linkMatches($keywordsMatches);
            $this->linker->linkCounterMatches($keywordsMatches);
            $this->linker->linkCounterMatches($noKeywordsMatches);
        } else {
            $keywordsMatches = $matchesQueryKeywords->all();
            $noKeywordsMatches = $matchesQueryNoKeywords->all();

            $matchesCount = count($noKeywordsMatches);

            $this->linker->linkCounterMatches($keywordsMatches);
            $this->linker->linkMatches($noKeywordsMatches);
            $this->linker->linkCounterMatches($noKeywordsMatches);
        }

        return $matchesCount;
    }

    private function prepareMainQuery(): ActiveQuery
    {
        return AdSearch::find()
            ->excludeUserId($this->model->user_id)
            ->live()
            ->andWhere(["{$this->comparingTable}.section" => $this->model->section])
            ->andWhere("ST_Distance_Sphere(
                    POINT({$this->model->location_lon}, {$this->model->location_lat}),
                    POINT({$this->comparingTable}.location_lon, {$this->comparingTable}.location_lat)
                ) <= 1000 * ({$this->comparingTable}.pickup_radius + {$this->model->delivery_radius})");
    }
}
