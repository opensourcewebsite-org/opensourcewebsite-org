<?php

use app\components\helpers\DebtHelper;
use app\helpers\Number;
use app\models\DebtRedistribution;
use Codeception\Configuration;
use Codeception\Example;
use Codeception\Util\Autoload;
use Helper\debt\redistribution\Common;

class RedistributionMaxAmountCest
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
     *
     * @example [123.45, 67.89]
     * @example [123]
     * @example [1000.11, 4000.44]
     *
     * @depends RedistributionPriorityCest:debtRedistributionPriorityDeny
     */
    public function chainLimitIsLowestLimitAmongMembers(FunctionalTester $I, Example $example): void
    {
        $this->validateLimitsSumNotGreaterTargetAmount($example);

        $scale = DebtHelper::getFloatScale();
        $chain1BalanceAmount = $this->common->balanceBefore[Common::CHAIN_1]->amount;
        $limit1 = rand(1, floor($chain1BalanceAmount));//any value when (contactLimit <= contactBalance) - will deny
        $this->common->setMaxAmountLimit($I, Common::CHAIN_1, true, $limit1);

        $this->common->setMaxAmountLimit($I, Common::CHAIN_2, true, $example[0]);
        if (isset($example[1])) {
            $this->common->setMaxAmountLimit($I, Common::CHAIN_2, false, $example[1]);
            $lowestLimit = min($example[0], $example[1]);
        } else {
            $lowestLimit = $example[0];
        }

        $this->common->testDefault($I, 2);

        $changedChain255 = Number::floatSub($this->common->getTargetAmount(), $lowestLimit, $scale);

        $this->common->expectBalanceNotChangedByKey($I, Common::CHAIN_1);
        $this->common->expectBalanceChangedByKey($I, Common::CHAIN_2, -$lowestLimit);
        $this->common->expectBalanceChangedByKey($I, Common::CHAIN_255, $changedChain255);
    }

    /**
     * @throws Throwable
     * @throws \yii\db\Exception
     *
     * @depends chainLimitIsLowestLimitAmongMembers
     */
    public function skipChainIfAnyMemberHasLimitDeny(FunctionalTester $I): void
    {
        //behavior of delete & DebtRedistribution::MAX_AMOUNT_DENY is the same
        $this->common->setMaxAmountLimit($I, Common::CHAIN_1, true, DebtRedistribution::MAX_AMOUNT_DENY);
        $this->common->getFixtureDebtRedistribution($I, Common::CHAIN_2,false)->delete();

        $this->common->testDefault($I, 1);

        $this->common->expectBalanceChangedByKey($I, Common::CHAIN_255, $this->common->getTargetAmount());
    }

    /**
     * @throws Throwable
     * @throws \yii\db\Exception
     *
     * @example [123.12, 456.45]
     * @example [999.99, 555.55]
     *
     * @depends skipChainIfAnyMemberHasLimitDeny
     */
    public function eachChainHasLimit(FunctionalTester $I, Example $example): void
    {
        $this->validateLimitsSumNotGreaterTargetAmount($example);

        $this->common->setMaxAmountLimit($I, Common::CHAIN_1, true, $example[0], true);
        $this->common->setMaxAmountLimit($I, Common::CHAIN_2, true, $example[1]);

        $this->common->testDefault($I, 3);

        $this->common->expectBalanceChangedByKey($I, Common::CHAIN_1, $example[0]);
        $this->common->expectBalanceChangedByKey($I, Common::CHAIN_2, -$example[1]);

        $scale = DebtHelper::getFloatScale();
        $expectBalanceToAdd = Number::floatSub($this->common->getTargetAmount(), $example[0], $scale);
        $expectBalanceToAdd = Number::floatSub($expectBalanceToAdd, $example[1], $scale);

        $this->common->expectBalanceChangedByKey($I, Common::CHAIN_255, $expectBalanceToAdd);
    }

    /**
     * @throws Throwable
     * @throws \yii\db\Exception
     *
     * @depends eachChainHasLimit
     */
    public function testCaseWhenLimitGreaterThanTargetAmount(FunctionalTester $I): void
    {
        $scale = DebtHelper::getFloatScale();
        $limitGreater = Number::floatAdd($this->common->getTargetAmount(), 1000, $scale);
        $this->common->setMaxAmountLimit($I, Common::CHAIN_1, true, $limit1Relative = 123.45, true);
        $this->common->setMaxAmountLimit($I, Common::CHAIN_2, true, $limitGreater);

        $this->common->testDefault($I, 2);

        $expectBalanceToAdd = Number::floatSub($this->common->getTargetAmount(), $limit1Relative, $scale);
        $this->common->expectBalanceChangedByKey($I, Common::CHAIN_1, $limit1Relative);
        $this->common->expectBalanceChangedByKey($I, Common::CHAIN_2, -$expectBalanceToAdd);
    }




    private function validateLimitsSumNotGreaterTargetAmount($example): void
    {
        $scale = DebtHelper::getFloatScale();
        $targetAmount = $this->common->getTargetAmount();

        $sum = 0;
        foreach ($example as $v) {
            $sum = Number::floatAdd($sum, $v, $scale);
        }

        if (Number::isFloatGreater($sum, $targetAmount, $scale)) {
            $message = "This test require: (target_amount > sum_of_example_values)\n";
            $message .= "Current: target_amount = $targetAmount; sum_of_example_values = $sum\n";
            $message .= "Solutions:\n";
            $message .= "1) Review other tests of this class. Maybe they can work with your example. (see `testCaseWhenLimitGreaterThanTargetAmount()`)\n";
            $message .= "2) Else, most likely, you should add new test.\n";
            $message .= "3) At last you can modify this test. It's unlikely case.\n";
            $message .= ' Do it only if test will remain simple. 2 simple tests better than 1 hardcore.';
            throw new \yii\base\InvalidArgumentException($message);
        }
    }
}
