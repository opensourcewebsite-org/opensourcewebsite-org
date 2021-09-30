<?php

declare(strict_types=1);

namespace app\modules\dataGenerator\components\generators;

use Yii;
use app\models\Company;
use app\models\CompanyUser;
use app\models\Currency;
use app\models\User;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use yii\db\ActiveRecord;
use yii\helpers\Console;

class CompanyUserFixture extends ARGenerator
{
    protected function factoryModel(): ?ActiveRecord
    {
        if (!$user = $this->getRandomUser()) {
            return null;
        }

        $model = new Company([
            'name' => $this->faker->company,
            'url' => $this->faker->url,
            'address' => $this->faker->address,
            'description' => $this->faker->realText(),
        ]);

        if (!$model->save()) {
            var_dump($model->errors);
            throw new ARGeneratorException(static::classNameModel() . ': can\'t save.' . "\r\n");
        }

        $model->link('users', $user, ['user_role' => CompanyUser::ROLE_OWNER]);

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
