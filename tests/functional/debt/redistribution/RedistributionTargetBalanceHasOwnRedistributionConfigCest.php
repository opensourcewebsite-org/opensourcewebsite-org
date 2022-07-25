<?php

use app\helpers\Number;
use app\models\Contact;
use app\models\DebtRedistribution;
use Codeception\Configuration;
use Codeception\Example;
use Codeception\Util\Autoload;
use Helper\debt\redistribution\Common;

class RedistributionTargetBalanceHasOwnRedistributionConfigCest
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
     * @depends RedistributionMaxAmountCest:testCaseWhenLimitGreaterThanTargetAmount
     */
    public function ownConfigHasHighestPriorityAmongChains(FunctionalTester $I): void
    {
        $this->createRedistributionConfigOwnToTargetBalance($I, 1, 123.45);

        $this->common->testDefault($I, 0);
        $this->common->expectBalanceNotChangedByKey($I, Common::CHAIN_2);
    }

    /**
     * @throws Throwable
     * @throws \yii\db\Exception
     *
     * @depends ownConfigHasHighestPriorityAmongChains
     *
     * @example [null]
     * @example [456.78]
     */
    public function ownConfigHasLowerPriorityAmongChains(FunctionalTester $I, Example $example): void
    {
        if ($example[0]) {
            $this->common->setMaxAmountLimit($I, Common::CHAIN_1, true, $example[0], true);
        }
        $this->createRedistributionConfigOwnToTargetBalance($I, 2, 123.45);

        $this->common->testDefault($I, 1, -$example[0]);
        $this->common->expectBalanceChangedByKey($I, Common::CHAIN_1, $example[0] ?: $this->common->getTargetAmount());
    }

    /**
     * @throws Throwable
     * @throws \yii\db\Exception
     *
     * @depends ownConfigHasLowerPriorityAmongChains
     *
     * @example [null]
     * @example [456.78]
     */
    public function ownConfigHasSamePriorityAmongChains(FunctionalTester $I, Example $example): void
    {
        if ($example[0]) {
            $this->common->setMaxAmountLimit($I, Common::CHAIN_1, true, $example[0], true);
        }
        $this->createRedistributionConfigOwnToTargetBalance($I, 1, $limitOwn = 123.45, false);

        $targetAmountToAdd = $example[0] ?: Number::floatSub($this->common->getTargetAmount(), $limitOwn, 2);

        $this->common->testDefault($I, 1, -$targetAmountToAdd);
        $this->common->expectBalanceChangedByKey($I, Common::CHAIN_1, $targetAmountToAdd);
    }

    /**
     * @throws Throwable
     * @throws \yii\db\Exception
     *
     * @depends ownConfigHasSamePriorityAmongChains
     */
    public function ownConfigIsFirstButDenyViaMaxAmount(FunctionalTester $I): void
    {
        $this->createRedistributionConfigOwnToTargetBalance($I, 1, DebtRedistribution::MAX_AMOUNT_DENY);

        $this->common->testDefault($I);
        $this->common->expectBalanceChangedByKey($I, Common::CHAIN_2, -$this->common->getTargetAmount());
    }

    /**
     * @throws Throwable
     * @throws \yii\db\Exception
     *
     * @depends ownConfigIsFirstButDenyViaMaxAmount
     */
    public function ownConfigIsFirstButDenyViaNotExistDebtRedistribution(FunctionalTester $I): void
    {
        $this->common->createContact($this->common->balanceBefore[Common::CHAIN_TARGET], 1);
        $this->common->setMaxAmountLimit($I, Common::CHAIN_1, true, DebtRedistribution::MAX_AMOUNT_DENY);

        $this->common->testDefault($I);
        $this->common->expectBalanceChangedByKey($I, Common::CHAIN_2, -$this->common->getTargetAmount());
    }

    /**
     * @throws Throwable
     * @throws \yii\db\Exception
     *
     * @depends ownConfigIsFirstButDenyViaNotExistDebtRedistribution
     */
    public function ownConfigIsFirstButDenyViaPriority(FunctionalTester $I): void
    {
        $this->common->createContact($this->common->balanceBefore[Common::CHAIN_TARGET], Contact::DEBT_REDISTRIBUTION_PRIORITY_DENY);
        $this->common->setMaxAmountLimit($I, Common::CHAIN_1, true, DebtRedistribution::MAX_AMOUNT_DENY);

        $this->common->testDefault($I);
        $this->common->expectBalanceChangedByKey($I, Common::CHAIN_2, -$this->common->getTargetAmount());
    }

    private function createRedistributionConfigOwnToTargetBalance(FunctionalTester $I, int $priority, $limit, bool $denySamePriorityChain = true): void
    {
        if ($denySamePriorityChain) {
            $mapPriority = [
                1 => Common::CHAIN_1,
                2 => Common::CHAIN_2,
            ];
            $this->common->setMaxAmountLimit($I, $mapPriority[$priority], true, DebtRedistribution::MAX_AMOUNT_DENY);
        }

        $contact_own_1 = $this->common->createContact($this->common->balanceBefore[Common::CHAIN_TARGET], $priority);

        $this->common->createDebtRedistribution($contact_own_1, $limit);
    }
}
