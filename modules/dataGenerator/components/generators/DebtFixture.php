<?php

namespace app\modules\dataGenerator\components\generators;

use app\models\Contact;
use app\modules\dataGenerator\models\Currency;
use app\models\Debt;
use app\models\SignupForm;
use app\models\User;
use Faker\Provider\DateTime;
use Yii;
use yii\db\ActiveRecord;
use yii\db\Exception;
use yii\helpers\Console;

class DebtFixture extends ARGenerator
{
    private const DATE_BETWEEN = [
        'min' => '-1 days',
        'max' => '+30 days',
    ];

    /**
     * @throws ARGeneratorException
     */
    public function init()
    {
        if (!Currency::find()->exists()) {
            throw new ARGeneratorException('Impossible to create Debt - there are no Currency in DB!');
        }
        parent::init();
    }

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

        $dateMin = self::DATE_BETWEEN['min'];
        $dateMax = self::DATE_BETWEEN['max'];
        $date    = self::getFaker()->optional()->dateTimeBetween($dateMin, $dateMax);

        $model = new Debt();

        $model->created_by      = $users['user_id'];
        $model->updated_by      = $users['user_id'];
        $model->currency_id     = $users['currency_id'];
        $model->valid_from_date = $date ? $date->format('Y-m-d') : null;
        $model->amount          = self::getFaker()->valid(static function ($v) { return (bool)$v; })->randomNumber();
        $model->status          = self::getFaker()->randomElement(Debt::mapStatus());
        $model->setUsersFromContact($users['user_id'], $users['link_user_id']);

        if ($model->valid_from_date) {
            $model->valid_from_time = self::getFaker()->optional()->time('H:i:s', '23:59:59');
        }

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
            ->virtual(false)
            ->orderByRandAlt(1)
            ->createCommand()
            ->queryOne();

        $currencyId = Currency::find()
            ->select('currency.id')
            ->orderByRandAlt(1)
            ->scalar();

        //looks like $currencyId should always be not empty. But, just in case, let's check it too.
        if (empty($contact) || !$currencyId) {
            $class = self::classNameModel();
            $msg   = "\n$class: creation skipped. No Contact exists\n";
            $msg   .= "It's not error - few iterations later new Contact will be generated.\n";
            Yii::$app->controller->stdout($msg, Console::BG_GREY);

            return [];
        }

        return $contact + ['currency_id' => $currencyId];
    }
}
