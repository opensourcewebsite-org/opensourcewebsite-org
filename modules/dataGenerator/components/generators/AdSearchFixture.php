<?php

declare(strict_types=1);

namespace app\modules\dataGenerator\components\generators;

use Yii;
use app\models\Currency;
use app\models\AdKeyword;
use app\models\matchers\ModelLinker;
use app\models\AdSearch;
use app\models\AdSection;
use app\models\User;
use app\helpers\LatLonHelper;
use yii\db\ActiveRecord;
use yii\helpers\Console;

class AdSearchFixture extends ARGenerator
{
    protected function factoryModel(): ?ActiveRecord
    {
        if (!$user = $this->getRandomUser()) {
            return null;
        }

        if (!$currency = $this->getRandomCurrency()) {
            return null;
        }

        $model = new AdSearch();

        $model->user_id = $user->id;
        $model->status = AdSearch::STATUS_ON;
        $model->section = AdSection::BUY_SELL;
        $model->title = $this->faker->sentence();
        $model->description = $this->faker->optional(0.5, null)->realText();

        if ($this->faker->boolean()) {
            $model->max_price = $this->faker->randomNumber(3);
            $model->currency_id = $currency->id;
        }

        $londonCenter = [51.509865, -0.118092];
        $location = LatLonHelper::generateRandomPoint($londonCenter, 200);

        $model->location_lat = $location[0];
        $model->location_lon = $location[1];

        $model->pickup_radius = $this->faker->optional(0.5, 0)->randomNumber(3);

        if ($this->save($model)) {
            if ($this->faker->boolean() && ($keywords = $this->getRandomKeywords())) {
                (new ModelLinker($model))->linkAll('keywords', $keywords);
            }
        }

        return $model;
    }

    /**
     * @return array<AdKeyword>
     */
    public function getRandomKeywords(): array
    {
        $keywordsCount = $this->faker->randomNumber(1);

        return AdKeyword::find()
            ->orderByRandAlt($keywordsCount)
            ->all();
    }
}
