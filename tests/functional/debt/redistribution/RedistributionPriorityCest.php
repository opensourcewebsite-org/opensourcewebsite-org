<?php

use app\models\Contact;
use Codeception\Configuration;
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

        $this->common->expectBalanceNotChangedByKey($I, Common::CHAIN_2);
        $this->common->expectBalanceChangedByKey($I, Common::CHAIN_1, $this->common->getTargetAmount());
    }

    /**
     * @throws Throwable
     * @throws \yii\db\Exception
     */
    public function debtRedistributionPriority_2(FunctionalTester $I): void
    {
        $this->denyChainPriority($I, 'Chain Priority #1. Member: 1st');

        $this->common->testDefault($I);

        $this->common->expectBalanceNotChangedByKey($I, Common::CHAIN_1);
        $this->common->expectBalanceChangedByKey($I, Common::CHAIN_2, -$this->common->getTargetAmount());
    }

    /**
     * @throws Throwable
     * @throws \yii\db\Exception
     */
    public function debtRedistributionPriority_255(FunctionalTester $I): void
    {
        $this->denyChainPriority($I, 'Chain Priority #1. Member: 1st');
        $this->denyChainPriority($I, 'Chain Priority #2. Member: 1st');

        $this->common->testDefault($I);

        $this->common->expectBalanceNotChangedByKey($I, Common::CHAIN_1);
        $this->common->expectBalanceNotChangedByKey($I, Common::CHAIN_2);

        $balanceChain255 = $this->common->getFixtureDebtRedistribution($I, 'Chain Priority #255. Member: 1st')->debtBalanceDirectionSame;
        $this->common->expectBalanceChanged($balanceChain255, 0, $this->common->getTargetAmount(), 255);
    }

    /**
     * @throws Throwable
     * @throws \yii\db\Exception
     */
    public function debtRedistributionPriorityDeny(FunctionalTester $I): void
    {
        $this->denyChainPriority($I, 'Chain Priority #1. Member: 1st');
        $this->denyChainPriority($I, 'Chain Priority #2. Member: 1st');
        $this->denyChainPriority($I, 'Chain Priority #255. Member: 1st');

        $this->common->testDefault($I, 0);

        $balanceChainDeny = $this->common->getFixtureDebtRedistribution($I, 'Chain Priority #0 (Deny). Member: 1st')->debtBalanceDirectionSame;
        expect('DebtBalance not exist. Chain: #0 (Deny)', $balanceChainDeny)->isEmpty();
    }




    private function denyChainPriority(FunctionalTester $I, $indexContact): void
    {
        $contact = $this->common->getFixtureContact($I, $indexContact);
        $contact->debt_redistribution_priority = Contact::DEBT_REDISTRIBUTION_PRIORITY_DENY;
        $contact->save();
    }
}
