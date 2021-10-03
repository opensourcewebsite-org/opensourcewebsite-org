<?php

namespace app\modules\dataGenerator\components\generators;

use Yii;
use app\models\Contact;
use app\modules\dataGenerator\models\Currency;
use app\models\Debt;
use app\models\SignupForm;
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
    protected function factoryModel(SignupForm $modelForm = null): ?ActiveRecord
    {
        $users = $this->findUsers();

        if (empty($users)) {
            return null;
        }

        $model = new Debt();

        /** @var BlameableBehavior $blameable */
        $blameable = $model->behaviors['blameable'];
        $blameable->defaultValue = static function (Event $event) {
            /** @var Debt $model */
            $model = $event->sender->owner;
            return $model->from_user_id;
        };

        $model->currency_id = $users['currency_id'];
        $model->amount = $this->faker->valid(static function ($v) {
            return (bool)$v;
        })->randomFloat(2, 1, 10000);
        $model->status = $this->faker->randomElement(Debt::mapStatus());
        $model->setUsersFromContact($users['user_id'], $users['link_user_id']);

        return $model;
    }

    /**
     * @return array
     * @throws Exception
     */
    private function findUsers(): array
    {
        /** @var array $contact choose random NOT virtual Contact */
        $contact = Contact::find()
            ->select('contact.user_id, contact.link_user_id')
            ->user()
            ->orderByRandAlt(1)
            ->createCommand()
            ->queryOne();

        $currencyId = Currency::find()
            ->select('currency.id')
            ->orderByRandAlt(1)
            ->scalar();

        //looks like $currencyId should always be not empty. But, just in case, let's check it too.
        if (empty($contact) || !$currencyId) {
            $message = "\n" . self::classNameModel() . ': creation skipped. There is no Contacts.' . "\n";
            Yii::$app->controller->stdout($message, Console::BG_GREY);

            return [];
        }

        return $contact + ['currency_id' => $currencyId];
    }
}
