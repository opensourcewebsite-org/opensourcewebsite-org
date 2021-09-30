<?php

declare(strict_types=1);

namespace app\modules\dataGenerator\components\generators;

use Yii;
use app\models\Currency;
use app\models\Gender;
use app\models\JobKeyword;
use app\models\matchers\ModelLinker;
use app\models\User;
use app\models\Vacancy;
use app\helpers\LatLonHelper;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use yii\db\ActiveRecord;
use yii\helpers\Console;

class VacancyFixture extends ARGenerator
{
    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    protected function factoryModel(): ?ActiveRecord
    {
        if (!$user = $this->getRandomUser()) {
            return null;
        }

        if (!($currency = $this->getRandomCurrency())) {
            return null;
        }

        $gender = $this->getRandomGender();

        $model = new Vacancy();

        $model->user_id = $user->id;
        $model->status = Vacancy::STATUS_ON;
        $model->remote_on = $this->faker->boolean();
        $model->name = $this->faker->jobTitle();
        $model->requirements = $this->faker->realText();
        $model->conditions = $this->faker->realText();
        $model->responsibilities = $this->faker->realText();

        if ($this->faker->boolean()) {
            $model->max_hourly_rate = $this->faker->randomNumber(2);
            $model->currency_id = $currency->id;
        }

        $model->gender_id =  ($this->faker->boolean() ? ($gender ? $gender->id : null) : null);

        if (!$model->remote_on || $this->faker->boolean()) {
            $londonCenter = [51.509865, -0.118092];
            $location = LatLonHelper::generateRandomPoint($londonCenter, 200);

            $model->location_lat = $location[0];
            $model->location_lon = $location[1];
        }

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

        return JobKeyword::find()
            ->orderByRandAlt($numOfKeywords)
            ->all();
    }

    private function getRandomGender(): ?Gender
    {
        /** @var Gender|null $gender */
        $gender = Gender::find()
            ->select('id')
            ->orderByRandAlt(1)
            ->one();

        return $gender;
    }
}
