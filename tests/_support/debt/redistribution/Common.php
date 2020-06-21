<?php

namespace Helper\debt\redistribution;

use app\components\debt\BalanceChecker;
use app\components\debt\Redistribution;
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
    public const CHAIN_TARGET = 'target';
    public const CHAIN_1 = 'chain1';
    public const CHAIN_2 = 'chain2';

    private const DEBT_FIXTURE_MAP = [
        self::CHAIN_TARGET => "It's balance should be redistributed",
        self::CHAIN_1 => "It's balance belongs to: Chain Priority #1. Member: 1st",
        self::CHAIN_2 => "It's balance belongs to: Chain Priority #2. Member: LAST",
    ];

    /** @var DebtBalance[] */
    public $balanceBefore = [];

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
        //This UPDATE emulate that Reduction was already fired.
        //See `DebtBalanceQuery::canBeRedistributed()`
        DebtBalance::getDb()->createCommand('UPDATE debt_balance SET reduction_try_at = 1;')->execute();

        foreach (self::DEBT_FIXTURE_MAP as $key => $indexFixture) {
            $this->balanceBefore[$key] = $this->findDebtBalanceByFixture($I, $indexFixture);
        }
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

    /**
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function testDefault(FunctionalTester $I, int $expectCountOfDebtGroups = 1, $targetAmountToAdd = null): void
    {
        (new Redistribution())->run();

        if (null !== $targetAmountToAdd) {
            $this->expectBalanceChangedByKey($I, Common::CHAIN_TARGET, $targetAmountToAdd);
        } elseif (0 === $expectCountOfDebtGroups) {
            $this->expectBalanceNotChangedByKey($I, self::CHAIN_TARGET);
        } else {
            $balanceTarget = $this->findDebtBalanceByFixture($I, "It's balance should be redistributed");
            $this->expectDebtBalanceBecomeZero($balanceTarget);
        }

        $this->expectCountOfDebtGroups($expectCountOfDebtGroups);
    }

    public function expectBalanceNotChanged(DebtBalance $balanceBefore, ?DebtBalance $balanceNow, $chainKey): void
    {
        expect("DebtBalance still exist. Chain: {{ $chainKey }}", $balanceNow)->notEmpty();

        $scale = DebtHelper::getFloatScale();
        /** @noinspection NullPointerExceptionInspection */
        $isEqual = Number::isFloatEqual($balanceBefore->amount, $balanceNow->amount, $scale);

        expect("DebtBalance was NOT redistributed. And was not changed. Chain: {{ $chainKey }}", $isEqual)->true();
    }

    public function expectBalanceNotChangedByKey(FunctionalTester $I, string $chainKey): void
    {
        $balance = $this->findDebtBalanceByFixture($I, self::DEBT_FIXTURE_MAP[$chainKey]);
        $this->expectBalanceNotChanged($this->balanceBefore[$chainKey], $balance, $chainKey);
    }

    public function expectBalanceChanged(?DebtBalance $balance, $amountWas, $amountToAdd, string $chainInfo): void
    {
        expect("DebtBalance still exist. Chain: {{ $chainInfo }}", $balance)->notEmpty();

        $scale = DebtHelper::getFloatScale();
        $expectBalance = Number::floatAdd($amountWas, $amountToAdd, $scale);
        /** @noinspection NullPointerExceptionInspection */
        $isEqual = Number::isFloatEqual($expectBalance, $balance->amount, $scale);

        /** @noinspection NullPointerExceptionInspection */
        expect("DebtBalance was: {{ $amountWas }}. Added: {{ $amountToAdd }}. Really: {{ $balance->amount }} Chain: {{ $chainInfo }}", $isEqual)->true();
    }

    public function expectBalanceChangedByKey(FunctionalTester $I, string $chainKey, $amountToAdd): void
    {
        $balance = $this->findDebtBalanceByFixture($I, self::DEBT_FIXTURE_MAP[$chainKey]);
        $this->expectBalanceChanged($balance, $this->balanceBefore[$chainKey]->amount, $amountToAdd, $chainKey);
    }

    public function getTargetAmount(): string
    {
        return $this->balanceBefore[Common::CHAIN_TARGET]->amount;
    }

    public function setMaxAmountLimit(FunctionalTester $I, $indexContact, $maxAmount): void
    {
        $model = $this->getFixtureDebtRedistribution($I, $indexContact);
        $model->max_amount = $maxAmount;
        $model->save();
    }
}
