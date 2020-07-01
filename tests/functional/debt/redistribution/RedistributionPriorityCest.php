<?php

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

        $this->common->testDefault($I);

        $this->common->expectBalanceNotChangedByKey($I, Common::CHAIN_1);
        $this->common->expectBalanceNotChangedByKey($I, Common::CHAIN_2);
        $this->common->expectBalanceChangedByKey($I, Common::CHAIN_255, $this->common->getTargetAmount());

        $balanceChain255 = $this->common->getFixtureDebtRedistribution($I, 'Chain Priority #255. Member: 1st')->debtBalanceDirectionSame;
        $this->common->expectBalanceChanged($balanceChain255, 0, $this->common->getTargetAmount(), 255);
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

        $debtRedistributionChainDeny = $this->common->getFixtureDebtRedistribution($I, Common::CHAIN_0_DENY, true);
        expect('DebtBalance not exist. Chain: #0 (Deny)', $debtRedistributionChainDeny->debtBalanceDirectionBack)->isEmpty();
    }
}
