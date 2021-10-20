<?php

declare(strict_types=1);

namespace app\modules\dataGenerator\components\generators;

use app\models\JobKeyword;
use yii\db\ActiveRecord;

class JobKeywordFixture extends ARGenerator
{
    protected function factoryModel(): ?ActiveRecord
    {
        $model = new JobKeyword([
            'keyword' => $this->faker->word(),
        ]);

        if (!$model->validate('keyword')) {
            return $this->factoryModel();
        }

        return $model;
    }
}
