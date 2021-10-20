<?php

namespace app\modules\dataGenerator\components\generators;

use Yii;
use app\models\Contact;
use app\modules\dataGenerator\models\Currency;
use app\models\Debt;
use app\models\forms\SignupForm;
use app\models\User;
use Faker\Provider\DateTime;
use yii\base\Event;
use yii\behaviors\BlameableBehavior;
use yii\db\ActiveRecord;
use yii\db\Exception;
use yii\helpers\Console;

class DebtFixture extends ARGenerator
{
    protected function providers(): array
    {
        return [DateTime::class];
    }

    /**
     * @param SignupForm|null $modelForm
     *
     * @return User
     * @throws ARGeneratorException
     * @throws Exception
     */
    protected function factoryModel(): ?ActiveRecord
    {
        if (!$users = $this->getRandomUsers(2)) {
            return null;
        }

        if (!$currency = $this->getRandomCurrency()) {
            return null;
        }

        $model = new Debt();

        $model->from_user_id = $users[0]->id;
        $model->to_user_id = $users[1]->id;
        $model->currency_id = $currency->id;
        $model->amount = $this->faker->randomFloat(2, 1, 100);
        $model->status = Debt::STATUS_CONFIRM;
        //$model->status = $this->faker->randomElement(Debt::mapStatus());
        $blameable = $model->behaviors['blameable'];
        $blameable->defaultValue = $this->faker->randomElement([$model->from_user_id, $model->to_user_id]);
        //$model->detachBehavior('blameable');
        //$model->created_by = $this->faker->randomElement([$model->from_user_id, $model->to_user_id]);
        //$model->updated_by = $model->created_by;

        return $model;
    }
}
