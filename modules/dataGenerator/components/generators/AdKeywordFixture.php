<?php

declare(strict_types=1);

namespace app\modules\dataGenerator\components\generators;

use app\models\AdKeyword;
use yii\db\ActiveRecord;

class AdKeywordFixture extends ARGenerator
{
    protected function factoryModel(): ?ActiveRecord
    {
        $model = new AdKeyword([
            'keyword' => $this->faker->word(),
        ]);

        if (!$model->validate('keyword')) {
            return $this->factoryModel();
        }

        return $model;
    }
}
