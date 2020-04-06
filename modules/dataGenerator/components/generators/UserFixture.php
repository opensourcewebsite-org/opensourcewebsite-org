<?php

namespace app\modules\dataGenerator\components\generators;

use app\models\SignupForm;
use app\models\User;
use Faker\Provider\Internet;
use yii\db\ActiveRecord;

class UserFixture extends ARGenerator
{
    private const PASSWORD = 'DataGenerator_0@';

    protected function providers(): array
    {
        return [Internet::class];
    }

    /**
     * @return User
     * @throws ARGeneratorException
     */
    public function load(): ActiveRecord
    {
        /** @var User $model */
        $model = parent::load();

        $model->setActive(); //can't do it before 1st save, because User::beforeSave() overwrite is_authenticated
        if ($model->save()) {
            return $model;
        }

        throw new ARGeneratorException($model);
    }

    /**
     * @param SignupForm|null $modelForm
     *
     * @return User
     * @throws ARGeneratorException
     */
    protected function factoryModel(SignupForm $modelForm = null): ActiveRecord
    {
        $modelForm = $modelForm ?? new SignupForm();

        //I decided not to use Generator::unique() modifier on email generator. Because:
        //  1. Module dataGenerator designed to run continuously. So we should optimize memory, not CPU.
        //  2. SignupForm::validate() will check is email unique anyway.
        $modelForm->email    = self::getFaker()->freeEmail;
        $modelForm->password = self::PASSWORD;

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