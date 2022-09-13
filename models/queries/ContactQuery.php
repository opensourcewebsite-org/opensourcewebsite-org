<?php

declare(strict_types=1);

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
 * @method Contact[] all()
 * @method null|array|Contact one()
 */
class ContactQuery extends ActiveQuery
{
    use RandomTrait;

    use SelfSearchTrait;

    /**
     * @return self
     */
    public function user(string $method = 'andWhere'): self
    {
        return $this->$method(['not', [Contact::tableName() . '.link_user_id' => null]]);
    }

    /**
     * @return self
     */
    public function nonUser(string $method = 'andWhere'): self
    {
        return $this->$method([Contact::tableName() . '.link_user_id' => null]);
    }

    /**
     * @return self
     */
    public function userOwner($id = null, $method = 'andWhere'): self
    {
        return $this->$method([Contact::tableName() . '.user_id' => ($id ?? Yii::$app->user->id)]);
    }

    /**
     * @return self
     */
    public function userLinked($id, $operand = 'IN', $method = 'andWhere'): self
    {
        return $this->$method([$operand, Contact::tableName() . '.link_user_id', $id]);
    }

    /**
     * @return self
     */
    public function forDebtRedistribution($contactId): self
    {
        return $this
            ->where([
                'id' => $contactId,
            ])
            ->userOwner()
            ->user();
    }

    /**
     * @return self
     */
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
     * @return self
     */
    public function canRedistributeInto(DebtBalance $debtBalance, ?int $level): self
    {
        DebtRedistribution::find()
            ->maxAmount(DebtRedistribution::MAX_AMOUNT_ANY, 'andWhere', $maxAmountIsAny);
        $maxAmountIsGreater = 'debt_redistribution.max_amount > debt_balance.amount';
        $balanceIsNotExist = 'debt_balance.currency_id IS NULL';

        $query = $this->andWhere('contact.debt_redistribution_priority <> ' . Contact::DEBT_REDISTRIBUTION_PRIORITY_DENY)
            ->withDebtRedistributionByCurrency($debtBalance->currency_id, 'INNER JOIN')
            ->joinWith('debtRedistributionByDebtorCustom.counterDebtBalance')
            ->andWhere("($maxAmountIsAny) OR ($balanceIsNotExist) OR ($maxAmountIsGreater)");

        if ($level === 1 && $debtBalance->hasRedistributionConfig()) {
            //deny redistribute first balance into contacts with lower priority
            $params = [':firstContactPriority' => $debtBalance->toContact->debt_redistribution_priority];
            $query->andWhere('contact.debt_redistribution_priority <= :firstContactPriority', $params);
        }

        return $query;
    }
}
