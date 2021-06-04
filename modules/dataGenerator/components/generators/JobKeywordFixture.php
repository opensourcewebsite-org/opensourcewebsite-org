<?php
declare(strict_types=1);

namespace app\modules\dataGenerator\components\generators;

use app\models\JobKeyword;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use yii\db\ActiveRecord;

class JobKeywordFixture extends ARGenerator
{
    private Generator $faker;

    public function __construct($config = [])
    {
        $this->faker = FakerFactory::create();

        parent::__construct($config);
    }

    protected function factoryModel(): ?ActiveRecord
    {
        $model = new JobKeyword([
            'keyword' => $this->faker->word(),
        ]);

        if (!$model->save()) {
            throw new ARGeneratorException("Can't save " . static::classNameModel() . "!\r\n");
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
}
