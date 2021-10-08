<?php

namespace app\modules\dataGenerator\components\generators;

use app\models\SignupForm;
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

        $modelForm->username = $this->faker->userName;
        $modelForm->password = $modelForm->username;
        $modelForm->password_repeat = $modelForm->password;

        if ($modelForm->validate()) {
            return $modelForm->factoryUser();
        }

        $errors = $modelForm->errors;

        unset($errors['username']);

        if (!empty($errors)) {
            //error either in changed  password rules, or new required fields were added
            throw new ARGeneratorException($modelForm);
        }

        //invalid email. regenerate it
        return $this->factoryModel($modelForm);
    }
}
