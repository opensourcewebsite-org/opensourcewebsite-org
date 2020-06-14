<?php

namespace Helper\debt\redistribution;

use app\components\debt\BalanceChecker;
use app\components\helpers\DebtHelper;
use app\helpers\Number;
use app\models\Contact;
use app\models\Debt;
use app\models\DebtBalance;
use app\models\DebtRedistribution;
use app\tests\fixtures\ContactFixture;
use app\tests\fixtures\DebtFixture;
use app\tests\fixtures\DebtRedistributionFixture;
use app\tests\fixtures\UserFixture;
use FunctionalTester;

/**
 * To check Redistribution system we must reload fixtures before each test.
 * So we need to place each test into separate Cest class.
 * That's why was created this class.
 */
class Common
{
    // fixture data located in tests/_data/*.php
    private function fixtures()
    {
        return [
            'user' => [
                'class' => UserFixture::className(),
                'dataFile' => codecept_data_dir() . 'debt/user.php',
            ],
            'contact' => [
                'class' => ContactFixture::className(),
                'dataFile' => codecept_data_dir() . 'debt/contact.php',
            ],
            'debt_redistribution' => [
                'class' => DebtRedistributionFixture::className(),
                'dataFile' => codecept_data_dir() . 'debt/debt_redistribution.php',
            ],
            'debt' => [
                'class' => DebtFixture::className(),
                'dataFile' => codecept_data_dir() . 'debt/debt.php',
            ],
        ];
    }

    /**
     * @throws \yii\db\Exception
     */
    public function _before(FunctionalTester $I)
    {
        $I->haveFixtures($this->fixtures());
        DebtBalance::getDb()->createCommand('UPDATE debt_balance SET processed_at = NULL;')->execute();
    }

    /**
     * @throws \yii\db\Exception
     */
    public function _after()
    {
        $errors = (new BalanceChecker)->run();
        expect('BalanceChecker found no bugs in DB', $errors)->equals([]);
    }

    public function expectDebtBalanceBecomeZero(?DebtBalance $debtBalance)
    {
        $test = 'Target DebtBalance was redistributed completely';

        if ($debtBalance && DebtBalance::STORE_EMPTY_AMOUNT) {
            $isRedistributedCompletely = Number::isFloatEqual($debtBalance->amount, 0, DebtHelper::getFloatScale());
        } else {
            $isRedistributedCompletely = !$debtBalance;
        }

        expect($test, $isRedistributedCompletely)->true();
    }

    public function expectCountOfDebtGroups(int $count)
    {
        $test = "Exact count of groups (chains) of Debts were performed: {{ $count }}";

        $debtGroups = Debt::find()
            ->select('group, COUNT(*) AS n')
            ->groupCondition(null, 'IS NOT')
            ->groupBy('group')
            ->asArray()
            ->all();

        expect($test, $debtGroups)->count($count);
    }

    public function getFixtureDebt(FunctionalTester $I, $index): Debt
    {
        return $I->grabFixture('debt', $index);
    }

    public function getFixtureContact(FunctionalTester $I, $index): Contact
    {
        return $I->grabFixture('contact', $index);
    }

    public function getFixtureDebtRedistribution(FunctionalTester $I, $indexContact): DebtRedistribution
    {
        return $I->grabFixture('debt_redistribution', $indexContact);
    }

    public function findDebtBalanceByFixture(FunctionalTester $I, $indexDebt): ?DebtBalance
    {
        $debtBalance = $this->getFixtureDebt($I, $indexDebt)->getDebtBalance();
        return $debtBalance->refresh() ? clone $debtBalance : null;
    }
}
