<?php

use app\components\helpers\DebtHelper;
use app\helpers\Number;
use app\models\DebtRedistribution;
use Codeception\Configuration;
use Codeception\Example;
use Codeception\Util\Autoload;
use Helper\debt\redistribution\Common;

class MaxAmountCest
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
     */
    public function chainLimitIsLowestLimitAmongMembers(FunctionalTester $I, Example $example): void
    {
        $this->validateLimitsSumNotGreaterTargetAmount($example);

        $this->common->setMaxAmountLimit($I, 'Chain Priority #1. Member: 1st', $example[0]);
        if (isset($example[1])) {
            $this->common->setMaxAmountLimit($I, 'Chain Priority #1. Member: LAST', $example[1]);
            $lowestLimit = min($example[0], $example[1]);
        } else {
            $lowestLimit = $example[0];
        }

        $this->common->testDefault($I, 2);

        $changedChain2 = Number::floatSub($this->common->getTargetAmount(), $lowestLimit, DebtHelper::getFloatScale());

        $this->common->expectBalanceChangedByKey($I, Common::CHAIN_1, $lowestLimit);
        $this->common->expectBalanceChangedByKey($I, Common::CHAIN_2, -$changedChain2);
    }

    /**
     * @throws Throwable
     * @throws \yii\db\Exception
     */
    public function skipChainIfAnyMemberHasLimitDeny(FunctionalTester $I): void
    {
        $this->common->setMaxAmountLimit($I, 'Chain Priority #1. Member: 1st', DebtRedistribution::MAX_AMOUNT_DENY);

        $this->common->testDefault($I, 1);

        $this->common->expectBalanceChangedByKey($I, Common::CHAIN_2, -$this->common->getTargetAmount());
    }

    /**
     * @throws Throwable
     * @throws \yii\db\Exception
     *
     * @example [123.12, 456.45]
     * @example [456.45, 123.12]
     */
    public function eachChainHasOwnLimit(FunctionalTester $I, Example $example): void
    {
        $this->validateLimitsSumNotGreaterTargetAmount($example);

        $this->common->setMaxAmountLimit($I, 'Chain Priority #1. Member: 1st', $example[0]);
        $this->common->setMaxAmountLimit($I, 'Chain Priority #2. Member: 1st', $example[1]);

        $this->common->testDefault($I, 3);

        $this->common->expectBalanceChangedByKey($I, Common::CHAIN_1, $example[0]);
        $this->common->expectBalanceChangedByKey($I, Common::CHAIN_2, -$example[1]);

        $scale = DebtHelper::getFloatScale();
        $expectBalanceToAdd = Number::floatSub($this->common->getTargetAmount(), $example[0], $scale);
        $expectBalanceToAdd = Number::floatSub($expectBalanceToAdd, $example[1], $scale);
        $balanceChain255 = $this->common->getFixtureDebtRedistribution($I, 'Chain Priority #255. Member: 1st')->debtBalanceDirectionSame;

        $this->common->expectBalanceChanged($balanceChain255, 0, $expectBalanceToAdd, 255);
    }

    /**
     * @throws Throwable
     * @throws \yii\db\Exception
     */
    public function testCaseWhenLimitGreaterThanTargetAmount(FunctionalTester $I): void
    {
        $this->common->setMaxAmountLimit($I, 'Chain Priority #1. Member: 1st', 123.45);
        $this->common->setMaxAmountLimit($I, 'Chain Priority #2. Member: 1st', 999999.99);

        $this->common->testDefault($I, 2);

        $this->common->expectBalanceChangedByKey($I, Common::CHAIN_1, 123.45);

        $scale = DebtHelper::getFloatScale();
        $expectBalanceToAdd = Number::floatSub($this->common->getTargetAmount(), 123.45, $scale);

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
            $message .= "1) Review other tests of this class. Maybe they can work with your example.\n";
            $message .= "2) Else, most likely, you should add new test.\n";
            $message .= "3) At last you can modify this test. It's unlikely case.\n";
            $message .= ' Do it only if test will remain simple. 2 simple tests better than 1 hardcore.';
            throw new \yii\base\InvalidArgumentException($message);
        }
    }
}
