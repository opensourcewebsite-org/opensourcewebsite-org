<?php
declare(strict_types=1);

namespace app\modules\dataGenerator\components\generators;

use Yii;
use app\models\Currency;
use app\models\JobKeyword;
use app\models\matchers\ModelLinker;
use app\models\Resume;
use app\models\User;
use app\helpers\LatLonHelper;
use yii\db\ActiveRecord;
use yii\helpers\Console;

class ResumeFixture extends ARGenerator
{
    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    public function init()
    {
        if (!Currency::find()->exists()) {
            throw new ARGeneratorException('Impossible to create ' . static::classNameModel() . ' - there are no Currency in DB!');
        }

        parent::init();
    }

    protected function factoryModel(): ?ActiveRecord
    {
        $user = $this->findUser();

        if (!($currency = $this->getRandomCurrency())) {
            $this->printNoCurrencyError();
            return null;
        }

        $model = new Resume();

        $model->user_id = $user->id;
        $model->status = Resume::STATUS_ON;
        $model->remote_on = $this->faker->boolean();
        $model->name = $this->faker->jobTitle();
        $model->skills = $this->faker->realText();
        $model->experiences = $this->faker->realText();
        $model->expectations = $this->faker->realText();

        if ($this->faker->boolean()) {
            $model->min_hourly_rate = $this->faker->randomNumber(2);
            $model->currency_id = $currency->id;
        }

        if (!$model->remote_on || $this->faker->boolean()) {
            $londonCenter = [51.509865, -0.118092];
            $location = LatLonHelper::generateRandomPoint($londonCenter, 200);

            $model->location_lat = $location[0];
            $model->location_lon = $location[1];

            $model->search_radius = $this->faker->randomNumber(3);
        }

        if (!$model->save()) {
            throw new ARGeneratorException("Can't save " . static::classNameModel() . "!\r\n");
        }

        if ($this->faker->boolean() && $keywords = $this->getRandomKeywords()) {
            (new ModelLinker($model))->linkAll('keywords', $keywords);
        }

        return $model;
    }

    /**
     * @throws ARGeneratorException
     */
    public function load(): ActiveRecord
    {
        return $this->factoryModel();
    }

    private function findUser(): ?User
    {
        /** @var User $user */
        $user = User::find()
            ->orderByRandAlt(1)
            ->one();

        if (!$user) {
            $class = self::classNameModel();
            $message = "\n$class: creation skipped. There is no Users\n";
            $message .= "It's not error - few iterations later new ExchangeOrder will be generated.\n";
            Yii::$app->controller->stdout($message, Console::BG_GREY);
        }

        return $user;
    }

    private function getRandomCurrency(): ?Currency
    {
        /** @var Currency|null $currency */
        $currency = Currency::find()
            ->select('id')
            ->where(['in', 'code', ['USD', 'EUR', 'RUB']])
            ->orderByRandAlt(1)
            ->one();

        return $currency;
    }

    private function printNoCurrencyError()
    {
        $class = self::classNameModel();
        $message = "\n$class: creation skipped. There is no Currencies yet.\n";
        $message .= "It's not error - few iterations later new ExchangeOrder will be generated.\n";
        Yii::$app->controller->stdout($message, Console::BG_GREY);
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
}
