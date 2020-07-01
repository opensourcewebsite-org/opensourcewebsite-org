<?php

use app\components\helpers\DebtHelper;
use app\helpers\Number;
use app\models\Contact;
use app\models\DebtRedistribution;
use Codeception\Configuration;
use Codeception\Example;
use Codeception\Util\Autoload;
use Helper\debt\redistribution\Common;

class RedistributionTargetBalanceHasOwnChainCest
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
    public function ownChainHasHighestPriorityAmongChains(FunctionalTester $I): void
    {
        $this->createChainOwnToTargetBalance($I, 1, 123.45);

        $this->common->testDefault($I, 0);
        $this->common->expectBalanceNotChangedByKey($I, Common::CHAIN_2);
    }

    /**
     * @throws Throwable
     * @throws \yii\db\Exception
     *
     * @depends ownChainHasHighestPriorityAmongChains
     *
     * @example [null]
     * @example [456.78]
     */
    public function ownChainHasLowerPriorityAmongChains(FunctionalTester $I, Example $example): void
    {
        if ($example[0]) {
            $this->common->setMaxAmountLimit($I, Common::CHAIN_1, true, $example[0], true);
        }
        $this->createChainOwnToTargetBalance($I, 2, 123.45);

        $this->common->testDefault($I, 1, -$example[0]);
        $this->common->expectBalanceChangedByKey($I, Common::CHAIN_1, $example[0] ?: $this->common->getTargetAmount());
    }

    /**
     * @throws Throwable
     * @throws \yii\db\Exception
     *
     * @depends ownChainHasLowerPriorityAmongChains
     *
     * @example [null]
     * @example [456.78]
     */
    public function ownChainHasSamePriorityAmongChains(FunctionalTester $I, Example $example): void
    {
        if ($example[0]) {
            $this->common->setMaxAmountLimit($I, Common::CHAIN_1, true, $example[0], true);
        }
        $this->createChainOwnToTargetBalance($I, 1, $limitOwn = 123.45, false);

        $scale = DebtHelper::getFloatScale();
        $targetAmountToAdd = $example[0] ?: Number::floatSub($this->common->getTargetAmount(), $limitOwn, $scale);

        $this->common->testDefault($I, 1, -$targetAmountToAdd);
        $this->common->expectBalanceChangedByKey($I, Common::CHAIN_1, $targetAmountToAdd);
    }

    /**
     * @throws Throwable
     * @throws \yii\db\Exception
     *
     * @depends ownChainHasSamePriorityAmongChains
     */
    public function ownChainIsFirstButDenyViaMaxAmount(FunctionalTester $I): void
    {
        $this->createChainOwnToTargetBalance($I, 1, DebtRedistribution::MAX_AMOUNT_DENY);

        $this->common->testDefault($I);
        $this->common->expectBalanceChangedByKey($I, Common::CHAIN_2, -$this->common->getTargetAmount());
    }

    /**
     * @throws Throwable
     * @throws \yii\db\Exception
     *
     * @depends ownChainIsFirstButDenyViaMaxAmount
     */
    public function ownChainIsFirstButDenyViaNotExistDebtRedistribution(FunctionalTester $I): void
    {
        $this->createContactForTargetBalance(1);
        $this->common->setMaxAmountLimit($I, Common::CHAIN_1, true, DebtRedistribution::MAX_AMOUNT_DENY);

        $this->common->testDefault($I);
        $this->common->expectBalanceChangedByKey($I, Common::CHAIN_2, -$this->common->getTargetAmount());
    }


    /**
     * @throws Throwable
     * @throws \yii\db\Exception
     *
     * @depends ownChainIsFirstButDenyViaNotExistDebtRedistribution
     */
    public function ownChainIsFirstButDenyViaPriority(FunctionalTester $I): void
    {
        $this->createContactForTargetBalance(Contact::DEBT_REDISTRIBUTION_PRIORITY_DENY);
        $this->common->setMaxAmountLimit($I, Common::CHAIN_1, true, DebtRedistribution::MAX_AMOUNT_DENY);

        $this->common->testDefault($I);
        $this->common->expectBalanceChangedByKey($I, Common::CHAIN_2, -$this->common->getTargetAmount());
    }







    private function createChainOwnToTargetBalance(FunctionalTester $I, int $priority, $limit, bool $denySamePriorityChain = true): void
    {
        if ($denySamePriorityChain) {
            $mapPriority = [
                1 => Common::CHAIN_1,
                2 => Common::CHAIN_2,
            ];
            $this->common->setMaxAmountLimit($I, $mapPriority[$priority], true, DebtRedistribution::MAX_AMOUNT_DENY);
        }

        $balanceTarget = $this->common->balanceBefore[Common::CHAIN_TARGET];
        $contact_own_1 = $this->createContactForTargetBalance($priority);

        $this->common->createDebtRedistribution($contact_own_1, $limit);

        $this->createDebtRedistribution($contact_own_1, $limit);

        $contact_own_last = new Contact();
        $contact_own_last->debt_redistribution_priority = $priority;
        $contact_own_last->link_user_id = $balanceTarget->from_user_id;
        $contact_own_last->user_id = $contact_own_1->link_user_id;
        $contact_own_last->name = "Contact for debt Redistribution chain. Own to target balance (have the same users). Priority: #$priority. Member: LAST";
        $contact_own_last->save();

        $this->common->createDebtRedistribution($contact_own_last, $limit);
    }

    private function createContactForTargetBalance(int $priority): Contact
    {
        $contact_own_1 = new Contact();
        $contact_own_1->debt_redistribution_priority = $priority;
        $contact_own_1->link_user_id = $this->common->balanceBefore[Common::CHAIN_TARGET]->from_user_id;
        $contact_own_1->user_id = $this->common->balanceBefore[Common::CHAIN_TARGET]->to_user_id;
        $contact_own_1->name = "Contact for debt Redistribution chain. Own to target balance (have the same users). Priority: #$priority. Member: 1st";
        $contact_own_1->save();

        return $contact_own_1;
    }
}
