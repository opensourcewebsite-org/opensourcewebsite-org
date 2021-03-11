<?php

namespace app\modules\dataGenerator\components\generators;

use Faker\Factory;
use Faker\Generator;
use yii\db\ActiveRecord;
use yii\test\Fixture;

abstract class ARGenerator extends Fixture
{
    /** @var Generator */
    static private $faker;

    /**
     * magic getter for `$this->faker`
     * initialize singleton faker
     * @return Generator
     */
    public static function getFaker(): Generator
    {
        if (!self::$faker) {
            self::$faker = Factory::create();
        }

        return self::$faker;
    }

    public function init()
    {
        $this->setProviders();
    }

    /**
     * @throws ARGeneratorException
     */
    public function load(): ActiveRecord
    {
        $model = $this->factoryModel();

        if ($model && !$model->save()) {
            throw new ARGeneratorException("Can't save ".static::classNameModel()."!\r\n");
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
        $faker = self::getFaker();

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
}
