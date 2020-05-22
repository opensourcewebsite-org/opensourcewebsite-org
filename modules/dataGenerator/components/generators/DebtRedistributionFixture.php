<?php

namespace app\modules\dataGenerator\components\generators;

use app\models\Contact;
use app\modules\dataGenerator\models\Currency;
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
        $contact = $this->findContact();

        if (!$contact) {
            return null;
        }

        $model = new DebtRedistribution();

        $model->setUsers($contact);
        $model->currency_id = $this->findCurrency($contact);
        $model->max_amount = self::getFaker()->optional()->randomFloat();

        return $model;
    }

    /**
     * @throws ARGeneratorException
     * @throws \yii\db\Exception
     */
    private function findContact(): ?Contact
    {
        $currencyQty = Currency::find()->count();

        /** @var Contact $contact pair of users, who can has additional DebtRedistribution */
        $contact = Contact::find()
            ->select('contact.user_id, contact.link_user_id, COUNT(debt_redistribution.currency_id) AS n_currency')
            ->joinWith('debtRedistributions')
            ->virtual(false)
            ->groupBy('contact.user_id, contact.link_user_id')
            ->having('n_currency < :currencyQty', [':currencyQty' => $currencyQty])
            ->orderBy('n_currency')
            ->limit(1)
            ->one();

        if (!$contact) {
            $class = self::classNameModel();
            $message = "\n$class: creation skipped. ";
            $message .= "Either no Contact exists, or all Contacts have full set of DebtRedistributions.\n";
            $message .= "It's not error - few iterations later new Contact will be generated.\n";
            Yii::$app->controller->stdout($message, Console::BG_GREY);

            return null;
        }

        return $contact;
    }

    /**
     * @throws ARGeneratorException
     */
    private function findCurrency(Contact $contact)
    {
        /** @var int $currencyId Currency, that was not used in DebtRedistributions yet */
        $currencyId = Currency::find()
            ->select('currency.id')
            ->joinWith([
                'debtRedistributions' => static function (DebtRedistributionQuery $query) use ($contact) {
                    $query->usersByModelSource($contact, 'andOnCondition');
                },
            ])
            ->andWhere('debt_redistribution.id IS NULL')
            ->limit(1)
            ->scalar();

        if (!$currencyId) {
            $message = 'Expected to find $currencyId. $contact=' . VarDumper::dumpAsString($contact->attributes);
            throw new ARGeneratorException($message);
        }

        return $currencyId;
    }
}
