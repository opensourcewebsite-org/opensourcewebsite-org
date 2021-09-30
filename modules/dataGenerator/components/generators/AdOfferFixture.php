<?php

declare(strict_types=1);

namespace app\modules\dataGenerator\components\generators;

use Yii;
use app\models\Currency;
use app\models\AdKeyword;
use app\models\matchers\ModelLinker;
use app\models\AdOffer;
use app\models\AdSection;
use app\models\User;
use app\helpers\LatLonHelper;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use yii\db\ActiveRecord;
use yii\helpers\Console;

class AdOfferFixture extends ARGenerator
{
    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    protected function factoryModel(): ?ActiveRecord
    {
        $user = $this->findUser();

        if (!($currency = $this->getRandomCurrency())) {
            $this->printNoCurrencyError();
            return null;
        }

        $model = new AdOffer();

        $model->user_id = $user->id;
        $model->status = AdOffer::STATUS_ON;
        $model->section = AdSection::BUY_SELL;
        $model->title = $this->faker->sentence();
        $model->description = $this->faker->boolean() ? $this->faker->realText() : null;

        if ($this->faker->boolean()) {
            $model->price = $this->faker->randomNumber(3);
            $model->currency_id = $currency->id;
        }

        $londonCenter = [51.509865, -0.118092];
        $location = LatLonHelper::generateRandomPoint($londonCenter, 200);

        $model->location_lat = $location[0];
        $model->location_lon = $location[1];

        $model->delivery_radius = $this->faker->boolean() ? $this->faker->randomNumber(3) : 0;

        if (!$model->save()) {
            throw new ARGeneratorException(static::classNameModel() . ': can\'t save.' . "\r\n");
        }

        if ($this->faker->boolean() && $keywords = $this->getRandomKeywords()) {
            (new ModelLinker($model))->linkAll('keywords', $keywords);
        }

        return $model;
    }

    /**
     * @throws ARGeneratorException
     */
    public function load(): ?ActiveRecord
    {
        return $this->factoryModel();
    }

    /**
     * @return array<JobKeyword>
     */
    public function getRandomKeywords(): array
    {
        $numOfKeywords = $this->faker->randomNumber(1);

        return AdKeyword::find()
            ->orderByRandAlt($numOfKeywords)
            ->all();
    }
}
