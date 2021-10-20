<?php

namespace app\modules\dataGenerator\components\generators;

use app\models\DebtRedistribution;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Console;

class DebtRedistributionFixture extends ARGenerator
{
    /**
     * @return DebtRedistribution|null
     * @throws ARGeneratorException
     * @throws \yii\db\Exception
     */
    protected function factoryModel(): ?ActiveRecord
    {
        if (!$users = $this->getRandomUsers(2)) {
            return null;
        }

        if (!$currency = $this->getRandomCurrency()) {
            return null;
        }

        $model = DebtRedistribution::find()
            ->where([
                'user_id' => $users[0]->id,
                'link_user_id' => $users[1]->id,
                'currency_id' => $currency->id,
            ])
            ->one();

        if (!$model) {
            $model = new DebtRedistribution();

            $model->user_id = $users[0]->id;
            $model->link_user_id = $users[1]->id;
            $model->currency_id = $currency->id;
        }

        $model->max_amount = $this->faker->optional()->numberBetween(0, 1000);

        $this->save($model);

        return $model;
    }
}
