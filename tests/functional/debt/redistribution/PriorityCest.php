<?php

use app\components\debt\Redistribution;
use app\components\helpers\DebtHelper;
use app\helpers\Number;
use app\models\Contact;
use app\models\DebtBalance;
use Codeception\Configuration;
use Codeception\Util\Autoload;
use Helper\debt\redistribution\Common;

class PriorityCest
{
    /** @var Common */
    protected $common;

    /** @var DebtBalance[] */
    private $balanceBefore = [
        'target' => null,
        'chain1' => null,
        'chain2' => null,
    ];

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

        $this->balanceBefore['target'] = $this->common->findDebtBalanceByFixture($I, "It's balance should be redistributed");
        $this->balanceBefore['chain1'] = $this->common->findDebtBalanceByFixture($I, "It's balance belongs to: Chain Priority #1. Member: 1st");
        $this->balanceBefore['chain2'] = $this->common->findDebtBalanceByFixture($I, "It's balance belongs to: Chain Priority #2. Member: LAST");
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
    public function testDebtRedistributionPriority_1(FunctionalTester $I): void
    {
        $this->testWrap($I, function (FunctionalTester $I) {
            $balanceChain2 = $this->common->findDebtBalanceByFixture($I, "It's balance belongs to: Chain Priority #2. Member: LAST");
            $this->balanceNotChanged($this->balanceBefore['chain2'], $balanceChain2, 2);

            $balanceChain1 = $this->common->findDebtBalanceByFixture($I, "It's balance belongs to: Chain Priority #1. Member: 1st");
            $this->balanceChanged($balanceChain1, $this->balanceBefore['chain1']->amount, $this->balanceBefore['target']->amount, 1);
        });
    }

    /**
     * @throws Throwable
     * @throws \yii\db\Exception
     */
    public function testDebtRedistributionPriority_2(FunctionalTester $I): void
    {
        $this->denyChainPriority($I, 'Chain Priority #1. Member: 1st');

        $this->testWrap($I, function (FunctionalTester $I) {
            $balanceChain1 = $this->common->findDebtBalanceByFixture($I, "It's balance belongs to: Chain Priority #1. Member: 1st");
            $this->balanceNotChanged($this->balanceBefore['chain1'], $balanceChain1, 1);

            $balanceChain2 = $this->common->findDebtBalanceByFixture($I, "It's balance belongs to: Chain Priority #2. Member: LAST");
            $this->balanceChanged($balanceChain2, $this->balanceBefore['chain2']->amount, -$this->balanceBefore['target']->amount, 2);
        });
    }

    /**
     * @throws Throwable
     * @throws \yii\db\Exception
     */
    public function testDebtRedistributionPriority_255(FunctionalTester $I): void
    {
        $this->denyChainPriority($I, 'Chain Priority #1. Member: 1st');
        $this->denyChainPriority($I, 'Chain Priority #2. Member: 1st');

        $this->testWrap($I, function (FunctionalTester $I) {
            $balanceChain1 = $this->common->findDebtBalanceByFixture($I, "It's balance belongs to: Chain Priority #1. Member: 1st");
            $this->balanceNotChanged($this->balanceBefore['chain1'], $balanceChain1, 1);

            $balanceChain2 = $this->common->findDebtBalanceByFixture($I, "It's balance belongs to: Chain Priority #2. Member: LAST");
            $this->balanceNotChanged($this->balanceBefore['chain2'], $balanceChain2, 2);

            $balanceChain255 = $this->common->getFixtureDebtRedistribution($I, 'Chain Priority #255. Member: 1st')->debtBalanceDirectionSame;
            $this->balanceChanged($balanceChain255, 0, $this->balanceBefore['target']->amount, 255);
        });
    }

    /**
     * @throws Throwable
     * @throws \yii\db\Exception
     */
    public function testDebtRedistributionPriorityDeny(FunctionalTester $I): void
    {
        $this->denyChainPriority($I, 'Chain Priority #1. Member: 1st');
        $this->denyChainPriority($I, 'Chain Priority #2. Member: 1st');
        $this->denyChainPriority($I, 'Chain Priority #255. Member: 1st');

        (new Redistribution())->run();

        $balanceTarget = $this->common->findDebtBalanceByFixture($I, "It's balance should be redistributed");
        $this->balanceNotChanged($this->balanceBefore['target'], $balanceTarget);

        $this->common->expectCountOfDebtGroups(0);
    }




    /**
     * @throws Throwable
     * @throws \yii\db\Exception
     */
    private function testWrap(FunctionalTester $I, callable $test): void
    {
        (new Redistribution())->run();

        $balanceTarget = $this->common->findDebtBalanceByFixture($I, "It's balance should be redistributed");
        $this->common->expectDebtBalanceBecomeZero($balanceTarget);

        $test($I);

        $this->common->expectCountOfDebtGroups(1);
    }

    private function denyChainPriority(FunctionalTester $I, $indexContact): void
    {
        $contact = $this->common->getFixtureContact($I, $indexContact);
        $contact->debt_redistribution_priority = Contact::DEBT_REDISTRIBUTION_PRIORITY_DENY;
        $contact->save();
    }

    private function balanceNotChanged(DebtBalance $balanceBefore, ?DebtBalance $balanceNow, $chainInfo = ''): void
    {
        $chainInfo = $chainInfo ? "Chain: {{ $chainInfo }}" : '';
        expect("DebtBalance still exist. $chainInfo", $balanceNow)->notEmpty();

        $scale = DebtHelper::getFloatScale();
        /** @noinspection NullPointerExceptionInspection */
        $isEqual = Number::isFloatEqual($balanceBefore->amount, $balanceNow->amount, $scale);

        expect("DebtBalance was NOT redistributed. And was not changed. $chainInfo", $isEqual)->true();
    }

    private function balanceChanged(?DebtBalance $balance, $amountWas, $amountToAdd, $chainInfo): void
    {
        expect("DebtBalance still exist. Chain: {{ $chainInfo }}", $balance)->notEmpty();

        $scale = DebtHelper::getFloatScale();
        $expectBalance = Number::floatAdd($amountWas, $amountToAdd, $scale);
        /** @noinspection NullPointerExceptionInspection */
        $isEqual = Number::isFloatEqual($expectBalance, $balance->amount, $scale);

        expect("DebtBalance was NOT redistributed. It was changed. Chain: {{ $chainInfo }}", $isEqual)->true();
    }
}
