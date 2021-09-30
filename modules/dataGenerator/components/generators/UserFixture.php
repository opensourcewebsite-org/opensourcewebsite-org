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

        //I decided not to use Generator::unique() modifier on email generator. Because:
        //  1. Module dataGenerator designed to run continuously. So we should optimize memory, not CPU.
        //  2. SignupForm::validate() will check is email unique anyway.
        $modelForm->email = $this->faker->email;
        $modelForm->password = $modelForm->email;

        if ($modelForm->validate()) {
            return $modelForm->factoryUser();
        }

        $errors = $modelForm->errors;

        unset($errors['email']);

        if (!empty($errors)) {
            //error either in changed  password rules, or new required fields were added
            throw new ARGeneratorException($modelForm);
        }

        //invalid email. regenerate it
        return $this->factoryModel($modelForm);
    }
}
