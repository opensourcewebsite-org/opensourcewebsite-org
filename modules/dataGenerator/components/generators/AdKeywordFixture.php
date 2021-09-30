<?php

declare(strict_types=1);

namespace app\modules\dataGenerator\components\generators;

use app\models\AdKeyword;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use yii\db\ActiveRecord;

class AdKeywordFixture extends ARGenerator
{
    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    protected function factoryModel(): ?ActiveRecord
    {
        $model = new AdKeyword([
            'keyword' => $this->faker->word(),
        ]);

        if (!$model->save()) {
            throw new ARGeneratorException(static::classNameModel() . ': can\'t save.' . "\r\n");
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
}
