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

        $adOfferQuery = $this->prepareMainQuery();

        $adOfferQueryNoKeywords = clone $adOfferQuery;

        $adOfferQueryNoKeywords = $adOfferQueryNoKeywords
            ->andWhere(['not in', $this->comparingTable . '.id', AdOfferKeyword::find()->select('ad_offer_id')]);

        $adOfferQueryKeywords = clone $adOfferQuery;

        $adOfferQueryKeywords = $adOfferQueryKeywords
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
            $keywordsMatches = $adOfferQueryKeywords->all();
            $noKeywordsMatches = $adOfferQueryNoKeywords->all();

            $matchedCount = count($keywordsMatches);

            $this->linker->linkMatches($keywordsMatches);
            $this->linker->linkCounterMatches($keywordsMatches);
            $this->linker->linkMatches($noKeywordsMatches);
        } else {
            $keywordsMatches = $adOfferQueryKeywords->all();
            $noKeywordsMatches = $adOfferQueryNoKeywords->all();

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
            ->where(['!=', $this->comparingTable . '.user_id', $this->model->user_id])
            ->andWhere([$this->comparingTable . '.status' => AdOffer::STATUS_ON])
            ->joinWith('user')
            ->andWhere(['>=', 'user.last_activity_at', time() - AdOffer::LIVE_DAYS * 24 * 60 * 60])
            ->andWhere([$this->comparingTable . '.section' => $this->model->section])
            ->andWhere(
                "ST_Distance_Sphere(
                    POINT({$this->model->location_lon}, {$this->model->location_lat}),
                    POINT(ad_offer.location_lon, ad_offer.location_lat)
                ) <= 1000 * (ad_offer.delivery_radius + {$this->model->pickup_radius})"
            );
    }
}
