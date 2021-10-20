<?php

namespace Helper\debt\redistribution;

use app\components\debt\DebtBalanceChecker;
use app\components\debt\Redistribution;
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

    public const DEBT_FIXTURE_MAP = [
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
        $this->markBalanceAsNeedReduction();

        foreach (self::DEBT_FIXTURE_MAP as $key => $indexFixture) {
            $this->balanceBefore[$key] = $indexFixture ? $this->findBalanceByFixtureDebt($I, $indexFixture) : new DebtBalance();
        }
    }

    public function expectBalanceBecomeZero(?DebtBalance $debtBalance)
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

    public function findBalanceByFixtureDebt(FunctionalTester $I, $indexDebt): ?DebtBalance
    {
        $debtBalance = $this->getFixtureDebt($I, $indexDebt)->getDebtBalance();
        return $debtBalance->refresh() ? clone $debtBalance : null;
    }

    public function findBalanceByChainMember(FunctionalTester $I, int $chainPriority, bool $isMemberFirst): ?DebtBalance
    {
        $debtBalance = $this->getFixtureDebtRedistribution($I, $chainPriority, $isMemberFirst)->counterDebtBalance;
        return ($debtBalance && $debtBalance->refresh()) ? clone $debtBalance : null;
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
            $balanceTarget = $this->findBalanceByFixtureDebt($I, self::DEBT_FIXTURE_MAP[self::CHAIN_TARGET]);
            $this->expectBalanceBecomeZero($balanceTarget);
        }

        $this->expectCountOfDebtGroups($expectCountOfDebtGroups);
    }

    public function expectBalanceNotChanged(DebtBalance $balanceBefore, ?DebtBalance $balanceNow, $chainKey): void
    {
        expect("DebtBalance should still exist. Chain: {{ $chainKey }}", $balanceNow)->notEmpty();

        /** @noinspection NullPointerExceptionInspection */
        $isEqual = Number::isFloatEqual($balanceBefore->amount, $balanceNow->amount, 2);

        expect("DebtBalance was NOT redistributed. And was not changed. Chain: {{ $chainKey }}", $isEqual)->true();
    }

    public function expectBalanceNotChangedByKey(FunctionalTester $I, $chainKey): void
    {
        if (self::DEBT_FIXTURE_MAP[$chainKey]) {
            $balance = $this->findBalanceByFixtureDebt($I, self::DEBT_FIXTURE_MAP[$chainKey]);
            $this->expectBalanceNotChanged($this->balanceBefore[$chainKey], $balance, $chainKey);
        } else {
            $balance = $this->findBalanceByChainMember($I, $chainKey, true);
            expect('DebtBalance should not exist', $balance)->isEmpty();
        }

        if (!is_numeric($chainKey)) {
            return;
        }

        $isMemberFirst = ($chainKey === self::CHAIN_2);
        $balance = $this->findBalanceByChainMember($I, $chainKey, $isMemberFirst);
        expect('DebtBalance (second) should not exist', $balance)->isEmpty();
    }

    public function expectBalanceChanged(?DebtBalance $balance, $amountWas, $amountToAdd, string $chainInfo): void
    {
        expect("DebtBalance should exist. Chain: {{ $chainInfo }}", $balance)->notEmpty();

        $expectBalance = Number::floatAdd($amountWas, $amountToAdd, 2);
        /** @noinspection NullPointerExceptionInspection */
        $isEqual = Number::isFloatEqual($expectBalance, $balance->amount, 2);

        $text = "DebtBalance was: {{ $amountWas }}. Should be added: {{ $amountToAdd }}.";
        /** @noinspection NullPointerExceptionInspection */
        $text .= " Amount now: {{ $balance->amount }}. Chain: {{ $chainInfo }}.";
        expect($text, $isEqual)->true();
    }

    public function expectBalanceChangedByKey(FunctionalTester $I, $chainKey, $amountToAdd): void
    {
        if (self::DEBT_FIXTURE_MAP[$chainKey]) {
            $balance = $this->findBalanceByFixtureDebt($I, self::DEBT_FIXTURE_MAP[$chainKey]);
        } else {
            $balance = $this->findBalanceByChainMember($I, $chainKey, true);
        }
        $this->expectBalanceChanged($balance, $this->balanceBefore[$chainKey]->amount, $amountToAdd, $chainKey);

        if (!is_numeric($chainKey)) {
            return;
        }

        $isMemberFirst = ($chainKey === self::CHAIN_2);
        $amountToAdd *= ($chainKey === self::CHAIN_2) ? -1 : 1;
        $balance = $this->findBalanceByChainMember($I, $chainKey, $isMemberFirst);
        $this->expectBalanceChanged($balance, 0, $amountToAdd, "$chainKey : next balance");
    }

    public function getTargetAmount(): string
    {
        return $this->balanceBefore[Common::CHAIN_TARGET]->amount;
    }

    public function setMaxAmountLimit(FunctionalTester $I, int $chainPriority, bool $isMemberFirst, $maxAmount, bool $calculateRelative = false): void
    {
        $model = $this->getFixtureDebtRedistribution($I, $chainPriority, $isMemberFirst);
        if ($calculateRelative && $maxAmount && $model->counterDebtBalance) {
            $maxAmount = Number::floatAdd($maxAmount, $model->counterDebtBalance->amount);
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

    public function createContact(DebtBalance $debtBalance, int $priority, bool $sameDirection = true): Contact
    {
        $contact_own_1 = new Contact();
        $contact_own_1->debt_redistribution_priority = $priority;
        $contact_own_1->link_user_id = $sameDirection ? $debtBalance->from_user_id : $debtBalance->to_user_id;
        $contact_own_1->user_id = $sameDirection ? $debtBalance->to_user_id : $debtBalance->from_user_id;
        $pk = implode(':', $debtBalance->primaryKey);
        $contact_own_1->name = "Contact Own to balance with PK=$pk.";
        $contact_own_1->save();

        return $contact_own_1;
    }

    public static function getContactKey(int $priority, bool $isMemberFirst): string
    {
        return "Chain Priority #$priority. Member: " . ($isMemberFirst ? '1st' : 'LAST');
    }

    /**
     * This UPDATE emulate that Reduction was already fired.
     * @see DebtBalanceQuery::canBeRedistributed()
     *
     * @throws \yii\db\Exception
     */
    public function markBalanceAsNeedReduction()
    {
        DebtBalance::getDb()->createCommand('UPDATE debt_balance SET reduction_try_at = 1, redistribute_try_at = null;')->execute();
    }

    public function createChain(DebtBalance $balanceToRedistribute, DebtBalance $balanceReceiver, ?int $priority = null): Contact
    {
        $contactTargetChainFirst = new Contact();
        if ($priority === null) {
            //priority of first chain member must be greater than priority of balance, which should be redistributed
            $priority = rand(1, $balanceToRedistribute->toContact->debt_redistribution_priority - 1);
        }
        $contactTargetChainFirst->debt_redistribution_priority = $priority;
        $contactTargetChainFirst->user_id = $balanceToRedistribute->from_user_id;
        $contactTargetChainFirst->link_user_id = $balanceReceiver->to_user_id;
        $contactTargetChainFirst->name = "Contact for debt Redistribution chain related to target balance. Member: 1st";
        $contactTargetChainFirst->save();

        $this->createDebtRedistribution($contactTargetChainFirst, DebtRedistribution::MAX_AMOUNT_ANY);

        //priority of other chain members can be any except Contact::DEBT_REDISTRIBUTION_PRIORITY_DENY (i.e. `rand(1, 255)`)
        //so we are using here the lowest possible - to check this rule
        $contactTargetChainLast = $this->createContact($balanceReceiver, Contact::DEBT_REDISTRIBUTION_PRIORITY_MAX);
        $this->createDebtRedistribution($contactTargetChainLast, DebtRedistribution::MAX_AMOUNT_ANY);

        return $contactTargetChainFirst;
    }
}
