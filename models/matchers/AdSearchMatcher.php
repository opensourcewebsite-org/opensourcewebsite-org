<?php

declare(strict_types=1);

namespace app\models\matchers;

use app\components\helpers\ArrayHelper;
use app\models\AdKeyword;
use app\models\AdOffer;
use app\models\AdOfferKeyword;
use app\models\AdSearch;
use app\models\matchers\interfaces\MatcherInterface;
use yii\db\ActiveQuery;

class AdSearchMatcher implements MatcherInterface
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

    public function match()
    {
        $this->linker->unlinkMatches();
        $matchesQuery = $this->prepareMainQuery();
        // Check a location
        $matchesQuery->andWhere(
            "ST_Distance_Sphere(
                POINT({$this->model->location_lon}, {$this->model->location_lat}),
                POINT({$this->comparingTable}.location_lon, {$this->comparingTable}.location_lat)
            ) <= (1000 * ({$this->comparingTable}.delivery_radius + {$this->model->pickup_radius}))"
        );
        // Check keywords
        $matchesQueryKeywords = clone $matchesQuery;
        $matchesQueryNoKeywords = clone $matchesQuery;
        // TODO improve
        $matchesQueryNoKeywords = $matchesQueryNoKeywords
            ->andWhere(['not in', $this->comparingTable . '.id', AdOfferKeyword::find()->select('ad_offer_id')]);

        if ($keywords = $this->model->keywords) {
            $keywordsIds = ArrayHelper::getColumn($keywords, 'id');
            $matchesQueryKeywords->joinWith('keywords');
            $matchesQueryKeywords->andWhere(['in', AdKeyword::tableName() . '.id', $keywordsIds]);

            $keywordsMatches = $matchesQueryKeywords->all();
            $noKeywordsMatches = $matchesQueryNoKeywords->all();

            $matches = $keywordsMatches;
            // TODO fix to only unique values
            $counterMatches = ArrayHelper::merge($keywordsMatches, $noKeywordsMatches);
        } else {
            $matches = $matchesQuery->all();
            $noKeywordsMatches = $matchesQueryNoKeywords->all();

            $counterMatches = $noKeywordsMatches;
        }

        $matchesCount = count($matches);

        $this->linker->linkMatches($matches);
        $this->linker->linkCounterMatches($counterMatches);

        return $matchesCount;
    }

    private function prepareMainQuery(): ActiveQuery
    {
        return AdOffer::find()
            ->excludeUserId($this->model->user_id)
            ->live()
            ->andWhere([$this->comparingTable . '.section' => $this->model->section]);
    }
}
