<?php

use app\components\debt\Redistribution;
use app\models\DebtBalance;
use Codeception\Configuration;
use Codeception\Example;
use Codeception\Util\Autoload;
use Helper\debt\redistribution\Common;

class RedistributionPriorityCest
{
    /** @var Common */
    protected $common;

    public function __construct()
    {
        Autoload::addNamespace('Helper\debt\redistribution', Configuration::supportDir() . 'debt/redistribution/');
    }

    protected function _inject(Common $common)
    {
        $this->common = $common;
    }

    /**
     * @throws \yii\db\Exception
     */
    public function _before(FunctionalTester $I)
    {
        $this->common->_before($I);
    }

    /**
     * @throws \yii\db\Exception
     */
    public function _after()
    {
        $this->common->_after();
    }




    /**
     * @throws Throwable
     * @throws \yii\db\Exception
     */
    public function debtRedistributionPriority_1(FunctionalTester $I): void
    {
        $this->common->testDefault($I);

        $this->common->expectBalanceChangedByKey($I, Common::CHAIN_1, $this->common->getTargetAmount());
        $this->common->expectBalanceNotChangedByKey($I, Common::CHAIN_2);
        $this->common->expectBalanceNotChangedByKey($I, Common::CHAIN_255);
    }

    /**
     * @throws Throwable
     * @throws \yii\db\Exception
     *
     * @depends debtRedistributionPriority_1
     */
    public function debtRedistributionPriority_2(FunctionalTester $I): void
    {
        $this->common->denyChainPriority($I, 'Chain Priority #1. Member: 1st');

        $this->common->testDefault($I);

        $this->common->expectBalanceNotChangedByKey($I, Common::CHAIN_1);
        $this->common->expectBalanceChangedByKey($I, Common::CHAIN_2, -$this->common->getTargetAmount());
        $this->common->expectBalanceNotChangedByKey($I, Common::CHAIN_255);
    }

    /**
     * @throws Throwable
     * @throws \yii\db\Exception
     *
     * @depends debtRedistributionPriority_2
     */
    public function debtRedistributionPriority_255(FunctionalTester $I): void
    {
        //if at least one contact is deny - chain is impossible
        $this->common->denyChainPriority($I, 'Chain Priority #1. Member: 1st');
        $this->common->denyChainPriority($I, 'Chain Priority #2. Member: LAST');

        $this->test255($I, $expectCountOfDebtGroups = 1, $changedChain = Common::CHAIN_255, $amountToAdd = $this->common->getTargetAmount());




        $I->wantToTest("On next Reduction running balance of changed chain #$changedChain should NOT redistributed back into target balance, if target balance has SAME or LOWER priority");

        /** @var DebtBalance $balance255First */
        $balance255First = $this->common->findBalanceByChainMember($I, $changedChain, true);
        expect("DebtBalance should exist. Chain: {{ $changedChain }}", $balance255First)->notEmpty();
        $balanceTarget = $this->common->balanceBefore[Common::CHAIN_TARGET];
        $contactTargetChainFirst = $this->common->createChain($balance255First, $balanceTarget, $changedChain);

        $this->common->markBalanceAsNeedReduction();
        //this run should affect nothing. All balances remain the same.
        $this->test255($I, $expectCountOfDebtGroups, $changedChain, $amountToAdd);




        $I->wantToTest("On next Reduction running balance of changed chain #$changedChain should BE redistributed back into target balance, if target balance has higher priority");

        $contactTargetChainFirst->debt_redistribution_priority = rand(1, $changedChain - 1);
        $contactTargetChainFirst->save();

        $this->common->markBalanceAsNeedReduction();
        (new Redistribution())->run();

        $balanceTarget = $this->common->findBalanceByFixtureDebt($I, Common::DEBT_FIXTURE_MAP[Common::CHAIN_TARGET]);

        $this->common->expectBalanceChanged($balanceTarget, 0, $amountToAdd, Common::CHAIN_TARGET);
        $this->common->expectBalanceNotChangedByKey($I, Common::CHAIN_1);
        $this->common->expectBalanceNotChangedByKey($I, Common::CHAIN_2);
        $this->common->expectCountOfDebtGroups($expectCountOfDebtGroups + 1);
        expect("DebtBalance should not exist. Chain: {{ $changedChain }}. Member: 1st", $balance255First->refresh())->false();
        $balance255Last = $this->common->findBalanceByChainMember($I, Common::CHAIN_255, false);
        expect('DebtBalance (second) should not exist. . Chain: {{ $changedChain }}. Member: LAST', $balance255Last)->isEmpty();
    }

    /**
     * @throws Throwable
     * @throws \yii\db\Exception
     *
     * @depends debtRedistributionPriority_255
     *
     * @example ["deny"]
     * @example ["Contact not exist"]
     */
    public function debtRedistributionPriorityDeny(FunctionalTester $I, Example $example): void
    {
        $delete = $example[0] === 'Contact not exist';
        $this->common->denyChainPriority($I, 'Chain Priority #1. Member: 1st', $delete);
        $this->common->denyChainPriority($I, 'Chain Priority #2. Member: 1st', $delete);
        $this->common->denyChainPriority($I, 'Chain Priority #255. Member: LAST', $delete);

        $this->common->testDefault($I, 0);

        $balance0 = $this->common->findBalanceByChainMember($I, Common::CHAIN_0_DENY, true);
        expect('DebtBalance not exist. Chain: #0 (Deny)', $balance0)->isEmpty();
    }

    /**
     * @throws Throwable
     * @throws \yii\db\Exception
     */
    private function test255(FunctionalTester $I, $expectCountOfDebtGroups, $changedChain, $amountToAdd)
    {
        $this->common->testDefault($I, $expectCountOfDebtGroups);

        $this->common->expectBalanceNotChangedByKey($I, Common::CHAIN_1);
        $this->common->expectBalanceNotChangedByKey($I, Common::CHAIN_2);
        $this->common->expectBalanceChangedByKey($I, $changedChain, $amountToAdd);
    }
}
