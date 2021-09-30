<?php

namespace app\modules\dataGenerator\components\generators;

use Faker\Factory;
use Faker\Generator;
use yii\db\ActiveRecord;
use yii\test\Fixture;
use app\models\User;
use app\models\Currency;

abstract class ARGenerator extends Fixture
{
    /** @var Generator */
    protected Generator $faker;

    public function __construct($config = [])
    {
        $this->faker = Factory::create();

        parent::__construct($config);
    }

    public function init()
    {
        $this->setProviders();
    }

    public static function getFaker(): Generator
    {
        return Factory::create();
    }

    /**
     * @throws ARGeneratorException
     */
    public function load(): ?ActiveRecord
    {
        $model = $this->factoryModel();

        if ($model && !$model->save()) {
            throw new ARGeneratorException(static::classNameModel() . ': can\'t save.' . "\r\n");
        }

        return $model;
    }

    public static function classNameModel(): string
    {
        $classFull  = explode('\\', static::class);
        $classShort = end($classFull);

        return str_replace('Fixture', '', $classShort);
    }

    abstract protected function factoryModel(): ?ActiveRecord;

    /**
     * Override it, if you need to specify some generators.
     * Example:
     * ```
     * return [\Faker\Provider\Internet::class];
     * ```
     * @return array
     */
    protected function providers(): array
    {
        return [];
    }

    private function setProviders(): void
    {
        $faker = $this->faker;

        foreach ($this->providers() as $class) {
            $isSet = false;

            /** @var \Faker\Provider\Base $provider */
            foreach ($faker->getProviders() as $provider) {
                if ($provider instanceof $class) {
                    $isSet = true;
                    break;
                }
            }

            if (!$isSet) {
                $faker->addProvider(new $class($faker));
            }
        }
    }

    protected function getRandomUser(): ?User
    {
        /** @var User $user */
        $user = User::find()
            ->orderByRandAlt(1)
            ->one();

        if (!$user) {
            $message = "\n" . self::classNameModel() . ': creation skipped. There is no Users.' . "\n";
            Yii::$app->controller->stdout($message, Console::BG_GREY);
        }

        return $user;
    }

    protected function getRandomCurrency(): ?Currency
    {
        /** @var Currency|null $currency */
        $currency = Currency::find()
            ->select('id')
            ->where(['in', 'code', ['USD', 'EUR', 'RUB']])
            ->orderByRandAlt(1)
            ->one();

        if (!$currency) {
            $message = "\n" . self::classNameModel() . ': creation skipped. There is no Currencies.' . "\n";
            Yii::$app->controller->stdout($message, Console::BG_GREY);
        }

        return $currency;
    }
}
