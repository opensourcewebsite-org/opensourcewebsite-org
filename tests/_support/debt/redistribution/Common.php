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
    /** @var int all tests should use the same currency */
    public const CURRENCY_USD = 108;

    public const CHAIN_TARGET = 'target';
    public const CHAIN_1 = 1;
    public const CHAIN_2 = 2;
    public const CHAIN_255 = 255;
    public const CHAIN_0_DENY = 0;

    private const DEBT_FIXTURE_MAP = [
        self::CHAIN_TARGET => "It's balance should be redistributed",
        self::CHAIN_1 => "It's balance belongs to: Chain Priority #1. Member: 1st",
        self::CHAIN_2 => "It's balance belongs to: Chain Priority #2. Member: LAST",
        self::CHAIN_255 => null,
        self::CHAIN_0_DENY => null,
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
            $this->balanceBefore[$key] = $indexFixture ? $this->findDebtBalanceByFixture($I, $indexFixture) : new DebtBalance();
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

        expect($test, $debtBalance)->isEmpty();
    }

    public function expectCountOfDebtGroups(int $count)
    {
        $test = "Exact count of groups (chains) of Debts should be: {{ $count }}";

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

    public function getFixtureDebtRedistribution(FunctionalTester $I, int $chainPriority, bool $isMemberFirst): DebtRedistribution
    {
        return $I->grabFixture('debt_redistribution', self::getContactKey($chainPriority, $isMemberFirst));
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

        if ($targetAmountToAdd) {
            $this->expectBalanceChangedByKey($I, Common::CHAIN_TARGET, $targetAmountToAdd);
        } elseif (0 === $expectCountOfDebtGroups) {
            $this->expectBalanceNotChangedByKey($I, self::CHAIN_TARGET);
        } else {
            $balanceTarget = $this->findDebtBalanceByFixture($I, self::DEBT_FIXTURE_MAP[self::CHAIN_TARGET]);
            $this->expectDebtBalanceBecomeZero($balanceTarget);
        }

        $this->expectCountOfDebtGroups($expectCountOfDebtGroups);
    }

    public function expectBalanceNotChanged(DebtBalance $balanceBefore, ?DebtBalance $balanceNow, $chainKey): void
    {
        expect("DebtBalance should still exist. Chain: {{ $chainKey }}", $balanceNow)->notEmpty();

        $scale = DebtHelper::getFloatScale();
        /** @noinspection NullPointerExceptionInspection */
        $isEqual = Number::isFloatEqual($balanceBefore->amount, $balanceNow->amount, $scale);

        expect("DebtBalance was NOT redistributed. And was not changed. Chain: {{ $chainKey }}", $isEqual)->true();
    }

    public function expectBalanceNotChangedByKey(FunctionalTester $I, $chainKey): void
    {
        if (self::DEBT_FIXTURE_MAP[$chainKey]) {
            $balance = $this->findDebtBalanceByFixture($I, self::DEBT_FIXTURE_MAP[$chainKey]);
            $this->expectBalanceNotChanged($this->balanceBefore[$chainKey], $balance, $chainKey);
        } else {
            $balance = $this->getFixtureDebtRedistribution($I, $chainKey, true)->debtBalanceDirectionBack;
            expect('DebtBalance should not exist', $balance)->isEmpty();
        }

        if (!is_numeric($chainKey)) {
            return;
        }

        $isMemberFirst = ($chainKey === self::CHAIN_2);
        $balance = $this->getFixtureDebtRedistribution($I, $chainKey, $isMemberFirst)->debtBalanceDirectionBack;
        expect('DebtBalance (second) should not exist', $balance)->isEmpty();
    }

    public function expectBalanceChanged(?DebtBalance $balance, $amountWas, $amountToAdd, string $chainInfo): void
    {
        expect("DebtBalance should exist. Chain: {{ $chainInfo }}", $balance)->notEmpty();

        $scale = DebtHelper::getFloatScale();
        $expectBalance = Number::floatAdd($amountWas, $amountToAdd, $scale);
        /** @noinspection NullPointerExceptionInspection */
        $isEqual = Number::isFloatEqual($expectBalance, $balance->amount, $scale);

        $text = "DebtBalance was: {{ $amountWas }}. Should be added: {{ $amountToAdd }}.";
        /** @noinspection NullPointerExceptionInspection */
        $text .= " Amount now: {{ $balance->amount }}. Chain: {{ $chainInfo }}.";
        expect($text, $isEqual)->true();
    }

    public function expectBalanceChangedByKey(FunctionalTester $I, $chainKey, $amountToAdd): void
    {
        if (self::DEBT_FIXTURE_MAP[$chainKey]) {
            $balance = $this->findDebtBalanceByFixture($I, self::DEBT_FIXTURE_MAP[$chainKey]);
        } else {
            $balance = $this->getFixtureDebtRedistribution($I, $chainKey, true)->debtBalanceDirectionBack;
        }
        $this->expectBalanceChanged($balance, $this->balanceBefore[$chainKey]->amount, $amountToAdd, $chainKey);

        if (!is_numeric($chainKey)) {
            return;
        }

        $isMemberFirst = ($chainKey === self::CHAIN_2);
        $amountToAdd *= ($chainKey === self::CHAIN_2) ? -1 : 1;
        $balance = $this->getFixtureDebtRedistribution($I, $chainKey, $isMemberFirst)->debtBalanceDirectionBack;
        $this->expectBalanceChanged($balance, 0, $amountToAdd, "$chainKey : next balance");
    }

    public function getTargetAmount(): string
    {
        return $this->balanceBefore[Common::CHAIN_TARGET]->amount;
    }

    public function setMaxAmountLimit(FunctionalTester $I, int $chainPriority, bool $isMemberFirst, $maxAmount, bool $calculateRelative = false): void
    {
        $model = $this->getFixtureDebtRedistribution($I, $chainPriority, $isMemberFirst);
        if ($calculateRelative && $maxAmount && $model->debtBalanceDirectionBack) {
            $maxAmount = Number::floatAdd($maxAmount, $model->debtBalanceDirectionBack->amount, DebtHelper::getFloatScale());
        }
        $model->max_amount = $maxAmount;
        $model->save();
    }

    /**
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function denyChainPriority(FunctionalTester $I, $indexContact, bool $delete = false): void
    {
        $contact = $this->getFixtureContact($I, $indexContact);
        if ($delete) {
            $contact->delete();
        } else {
            $contact->debt_redistribution_priority = Contact::DEBT_REDISTRIBUTION_PRIORITY_DENY;
            $contact->save();
        }
    }

    public function createDebtRedistribution(Contact $contact, $limit): void
    {
        $debtRedistribution = new DebtRedistribution();
        $debtRedistribution->setUsers($contact);
        $debtRedistribution->currency_id = Common::CURRENCY_USD;
        $debtRedistribution->max_amount = $limit;

        $debtRedistribution->save();
    }

    public static function getContactKey(int $priority, bool $isMemberFirst): string
    {
        return "Chain Priority #$priority. Member: " . ($isMemberFirst ? '1st' : 'LAST');
    }
}
