<?php

use app\components\helpers\DebtHelper;
use app\helpers\Number;
use app\models\Contact;
use app\models\DebtRedistribution;
use Codeception\Configuration;
use Codeception\Util\Autoload;
use Helper\debt\redistribution\Common;

class TargetBalanceHasOwnChainCest
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
    public function ownChainIsFirst(FunctionalTester $I): void
    {
        $this->createChainOwnToTargetBalance($I, 1, $limit = 123.45);

        $diff = Number::floatSub($this->common->getTargetAmount(), $limit, DebtHelper::getFloatScale());

        $this->common->testDefault($I, 1, -$diff);
        $this->common->expectBalanceChangedByKey($I, Common::CHAIN_2, -$diff);
    }

    /**
     * @throws Throwable
     * @throws \yii\db\Exception
     */
    public function ownChainIsNotFirst(FunctionalTester $I): void
    {
        $this->createChainOwnToTargetBalance($I, 2, 123.45);

        $this->common->testDefault($I);
        $this->common->expectBalanceChangedByKey($I, Common::CHAIN_1, $this->common->getTargetAmount());
    }

    /**
     * @throws Throwable
     * @throws \yii\db\Exception
     */
    public function ownChainIsFirstButDeny(FunctionalTester $I): void
    {
        $this->createChainOwnToTargetBalance($I, 1, DebtRedistribution::MAX_AMOUNT_DENY);

        $this->common->testDefault($I);
        $this->common->expectBalanceChangedByKey($I, Common::CHAIN_2, -$this->common->getTargetAmount());
    }

    /**
     * @throws Throwable
     * @throws \yii\db\Exception
     */
    public function ownChainIsUnlimitedAndFirst(FunctionalTester $I): void
    {
        $this->createChainOwnToTargetBalance($I, 1, DebtRedistribution::MAX_AMOUNT_ANY);

        $this->common->testDefault($I, 0);
    }

    /**
     * @throws Throwable
     * @throws \yii\db\Exception
     */
    public function ownChainIsUnlimitedButSecond(FunctionalTester $I): void
    {
        $this->common->setMaxAmountLimit($I, 'Chain Priority #1. Member: 1st', $limit = 123.45);
        $this->createChainOwnToTargetBalance($I, 2, DebtRedistribution::MAX_AMOUNT_ANY);

        $this->common->testDefault($I, 1, -$limit);
        $this->common->expectBalanceChangedByKey($I, Common::CHAIN_1, $limit);
    }

    /**
     * @throws Throwable
     * @throws \yii\db\Exception
     */
    public function ownChainIsLimitedAndSecond(FunctionalTester $I): void
    {
        $this->common->setMaxAmountLimit($I, 'Chain Priority #1. Member: 1st', $limit1 = 123.45);
        $this->createChainOwnToTargetBalance($I, 2, $limitOwn = 67.89);

        $scale = DebtHelper::getFloatScale();
        $diff1 = Number::floatSub($this->common->getTargetAmount(), $limitOwn, $scale);

        $this->common->testDefault($I, 2, -$diff1);
        $this->common->expectBalanceChangedByKey($I, Common::CHAIN_1, $limit1);

        $balanceChain255 = $this->common->getFixtureDebtRedistribution($I, 'Chain Priority #255. Member: 1st')->debtBalanceDirectionSame;
        $diff255 = Number::floatSub($diff1, $limit1, $scale);
        $this->common->expectBalanceChanged($balanceChain255, 0, $diff255, 255);
    }






    private function createChainOwnToTargetBalance(FunctionalTester $I, int $priority, $limit): void
    {
        $mapPriority = [
            1 => 'Chain Priority #1. Member: 1st',
            2 => 'Chain Priority #2. Member: 1st',
        ];
        $this->common->setMaxAmountLimit($I, $mapPriority[$priority], DebtRedistribution::MAX_AMOUNT_DENY);

        $balanceTarget = $this->common->balanceBefore[Common::CHAIN_TARGET];

        $contact_own_1 = new Contact();
        $contact_own_1->debt_redistribution_priority = $priority;
        $contact_own_1->link_user_id = $balanceTarget->from_user_id;
        $contact_own_1->user_id = $balanceTarget->to_user_id;
        $contact_own_1->name = "Contact for debt Redistribution chain. Own to target balance (have the same users). Priority: #$priority. Member: 1st";
        $contact_own_1->save();

        $this->createDebtRedistribution($contact_own_1, $limit);

        $contact_own_last = new Contact();
        $contact_own_last->debt_redistribution_priority = $priority;
        $contact_own_last->link_user_id = $balanceTarget->from_user_id;
        $contact_own_last->user_id = $contact_own_1->link_user_id;
        $contact_own_last->name = "Contact for debt Redistribution chain. Own to target balance (have the same users). Priority: #$priority. Member: LAST";
        $contact_own_last->save();

        $this->createDebtRedistribution($contact_own_last, $limit);
    }

    private function createDebtRedistribution(Contact $contact, $limit): void
    {
        $debtRedistribution = new DebtRedistribution();
        $debtRedistribution->user_id = $contact->link_user_id;
        $debtRedistribution->link_user_id = $contact->user_id;
        $debtRedistribution->currency_id = 108; //USD
        $debtRedistribution->max_amount = $limit;

        $debtRedistribution->save();
    }
}
