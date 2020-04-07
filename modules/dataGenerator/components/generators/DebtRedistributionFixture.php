<?php

namespace app\modules\dataGenerator\components\generators;

use app\models\Contact;
use app\models\Currency;
use app\models\DebtRedistribution;
use app\models\queries\DebtRedistributionQuery;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Console;
use yii\helpers\VarDumper;

class DebtRedistributionFixture extends ARGenerator
{
    /**
     * @return DebtRedistribution|null
     * @throws ARGeneratorException
     * @throws \yii\db\Exception
     */
    protected function factoryModel(): ?ActiveRecord
    {
        $users = $this->findUsers();

        if (empty($users)) {
            return null;
        }

        $model = new DebtRedistribution();

        $model->from_user_id = $users['user_id'];
        $model->to_user_id   = $users['link_user_id'];
        $model->currency_id  = $users['currency_id'];
        $model->max_amount   = self::getFaker()->optional()->randomFloat();

        return $model;
    }

    /**
     * @return array
     * @throws ARGeneratorException
     * @throws \yii\db\Exception
     */
    private function findUsers(): array
    {
        $currencyQty = Currency::find()->count();

        /** @var array $contact pair of users, who can has additional DebtRedistribution */
        $contact = Contact::find()
            ->select('contact.user_id, contact.link_user_id, COUNT(debt_redistribution.currency_id) AS n_currency')
            ->joinWith('debtRedistributions')
            ->virtual(false)
            ->groupBy('contact.user_id, contact.link_user_id')
            ->having('n_currency < :currencyQty', [':currencyQty' => $currencyQty])
            ->orderBy('n_currency')
            ->limit(1)
            ->createCommand()
            ->queryOne();

        if (empty($contact)) {
            $class = self::classNameModel();
            $msg   = "\n$class: creation skipped. ";
            $msg   .= "Either no Contact exists, or all Contacts have full set of DebtRedistributions.\n";
            $msg   .= "It's not error - few iterations later new Contact will be generated.\n";
            Yii::$app->controller->stdout($msg, Console::BG_GREY);

            return [];
        }

        /** @var int $currencyId Currency, that was not used in DebtRedistributions yet */
        $currencyId = Currency::find()
            ->select('currency.id')
            ->joinWith([
                'debtRedistributions' => function (DebtRedistributionQuery $q) use ($contact) {
                    $q->fromUser($contact['user_id'], 'andOnCondition');
                    $q->toUser($contact['link_user_id'], 'andOnCondition');
                },
            ])
            ->where('debt_redistribution.id IS NULL')
            ->limit(1)
            ->scalar();

        if (!$currencyId) {
            $msg = 'Expected to find $currencyId. $contact=' . VarDumper::dumpAsString($contact);
            throw new ARGeneratorException($msg);
        }

        return $contact + ['currency_id' => $currencyId];
    }
}
