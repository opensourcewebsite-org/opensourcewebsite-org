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

        $adSearchQuery = $this->prepareMainSearchQuery();

        $adSearchQueryNoKeywords = clone $adSearchQuery;
        $adSearchQueryNoKeywords = $adSearchQueryNoKeywords
            ->andWhere(['not in', "{$this->comparingTable}.id", AdSearchKeyword::find()->select('ad_search_id')]);

        $adSearchQueryKeywords = clone $adSearchQuery;
        $adSearchQueryKeywords = $adSearchQueryKeywords
            ->joinWith(['keywords' => function ($query) {
                $query
                    ->joinWith('adOffers')
                    ->andWhere([AdOffer::tableName() . '.id' => $this->model->id]);
            }])
            ->groupBy(AdSearch::tableName() . '.id');

        if ($this->model->getKeywords()->count() > 0) {
            $keywordsMatches = $adSearchQueryKeywords->all();
            $noKeywordsMatches = $adSearchQueryNoKeywords->all();

            $matchedCount = count($keywordsMatches);

            $this->linker->linkMatches($keywordsMatches);
            $this->linker->linkCounterMatches($keywordsMatches);
            $this->linker->linkCounterMatches($noKeywordsMatches);

        } else {
            $noKeywordsMatches = $adSearchQueryNoKeywords->all();

            $matchedCount = count($noKeywordsMatches);

            $this->linker->linkMatches($noKeywordsMatches);
            $this->linker->linkCounterMatches($noKeywordsMatches);
        }
        return $matchedCount;
    }

    private function prepareMainSearchQuery(): ActiveQuery
    {
        return AdSearch::find()
            ->where(['!=', "{$this->comparingTable}.user_id", $this->model->user_id])
            ->andWhere(["{$this->comparingTable}.status" => AdSearch::STATUS_ON])
            ->joinWith('user')
            ->andWhere(['>=', 'user.last_activity_at', time() - AdSearch::LIVE_DAYS * 24 * 60 * 60])
            ->andWhere(["{$this->comparingTable}.section" => $this->model->section])
            ->andWhere("ST_Distance_Sphere(
                    POINT({$this->model->location_lon}, {$this->model->location_lat}),
                    POINT(ad_search.location_lon, ad_search.location_lat)
                ) <= 1000 * (ad_search.pickup_radius + {$this->model->delivery_radius})");
    }
}
