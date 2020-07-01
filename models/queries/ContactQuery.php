<?php

namespace app\models\queries;

use app\models\Contact;
use app\models\DebtBalance;
use app\models\DebtRedistribution;
use app\models\queries\traits\RandomTrait;
use app\models\queries\traits\SelfSearchTrait;
use Yii;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[Contact]].
 *
 * @see Contact
 *
 * @method Contact[]          all()
 * @method null|array|Contact one()
 */
class ContactQuery extends ActiveQuery
{
    use RandomTrait;
    use SelfSearchTrait;

    public function virtual(bool $isVirtual, $method = 'andWhere'): self
    {
        if ($isVirtual) {
            $this->$method(['contact.link_user_id' => null]);
        } else {
            $this->$method(['IS NOT', 'contact.link_user_id', null]);
        }

        return $this;
    }

    public function userOwner($id = null, $method = 'andWhere'): self
    {
        return $this->$method(['contact.user_id' => $id ?? Yii::$app->user->id]);
    }

    public function userLinked($id, $operand = 'IN', $method = 'andWhere'): self
    {
        return $this->$method([$operand, 'contact.link_user_id', $id]);
    }

    public function forDebtRedistribution($contactId): self
    {
        return $this
            ->where(['id' => $contactId])
            ->userOwner()
            ->virtual(false);
    }

    public function withDebtRedistributionByCurrency($currencyId, $joinType = 'LEFT JOIN'): self
    {
        return $this->joinWith([
            'debtRedistributionByDebtorCustom' => static function (DebtRedistributionQuery $query) use ($currencyId) {
                $query->currency($currencyId, 'andOnCondition')
                    ->maxAmountIsNotDeny('andOnCondition');
            },
        ], true, $joinType);
    }

    /**
     * It can only if:
     * - it has DebtRedistribution;
     * - And DebtBalance.amount did not reached limit (DebtRedistribution.max_amount) yet.
     *
     * @return ContactQuery
     */
    public function canRedistributeInto(DebtBalance $debtBalance, ?int $level): self
    {
        DebtRedistribution::find()
            ->maxAmount(DebtRedistribution::MAX_AMOUNT_ANY, 'andWhere', $maxAmountIsAny);
        $maxAmountIsGreater = 'debt_redistribution.max_amount > debt_balance.amount';
        $balanceIsNotExist = 'debt_balance.currency_id IS NULL';

        $query = $this->andWhere('contact.debt_redistribution_priority <> ' . Contact::DEBT_REDISTRIBUTION_PRIORITY_DENY)
            ->withDebtRedistributionByCurrency($debtBalance->currency_id, 'INNER JOIN')
            ->joinWith('debtRedistributionByDebtorCustom.debtBalanceDirectionBack')
            ->andWhere("($maxAmountIsAny) OR ($balanceIsNotExist) OR ($maxAmountIsGreater)");

        if ($level === 1 && $debtBalance->hasRedistributionConfig()) {
            //deny redistribute first balance into contacts with lower priority
            $params = [':firstContactPriority' => $debtBalance->toContact->debt_redistribution_priority];
            $query->andWhere('contact.debt_redistribution_priority <= :firstContactPriority', $params);
        }

        return $query;
    }
}
