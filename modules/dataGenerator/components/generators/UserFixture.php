<?php

namespace app\modules\dataGenerator\components\generators;

use app\models\forms\SignupForm;
use app\models\User;
use Faker\Provider\Internet;
use yii\db\ActiveRecord;

class UserFixture extends ARGenerator
{
    protected function providers(): array
    {
        return [Internet::class];
    }

    /**
     * @param SignupForm|null $modelForm
     *
     * @return User
     * @throws ARGeneratorException
     */
    protected function factoryModel(SignupForm $modelForm = null): ?ActiveRecord
    {
        $modelForm = $modelForm ?? new SignupForm();

        $modelForm->username = $this->faker->userName();
        $modelForm->password = $modelForm->username;
        $modelForm->password_repeat = $modelForm->password;

        if ($modelForm->validate()) {
            return $modelForm->factoryUser();
        }

        //invalid username. regenerate it
        return $this->factoryModel($modelForm);
    }
}
