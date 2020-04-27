<?php

namespace app\models\queries;

use app\models\Debt;
use app\models\queries\traits\SelfSearchTrait;
use yii\db\ActiveQuery;
use app\models\DebtBalance;

/**
 * This is the ActiveQuery class for [[\app\models\DebtBalance]].
 *
 * @see DebtBalance
 * @method DebtBalance[] all()
 * @method null|array|DebtBalance one()
 */
class DebtBalanceQuery extends ActiveQuery
{
    use SelfSearchTrait;

    public function debt(Debt $debt): self
    {
        /**
         * @var [] $users
         * <li> We should use `IN` condition, because we can't know is direction (Credit|Deposit) of this particular
         * Debt is the same as direction of whole balance.
         * <li> Nevertheless we use `IN` condition, this query will always return 1 or 0 results. And never 2.
         */
        $users = [$debt->from_user_id, $debt->to_user_id];

        //order of attributes is important for optimization - it should match order of columns in PK
        return $this->where([
            'currency_id'  => $debt->currency_id,
            'from_user_id' => $users,
            'to_user_id'   => $users,
        ]);
    }

    /**
     * @param array|int $id
     * @param string $operand
     *
     * @return DebtBalanceQuery
     */
    public function userTo($id, $operand = 'IN'): self
    {
        return $this->andWhere([$operand, 'debt_balance.to_user_id', $id]);
    }

    public function notResolved(): self
    {
        return $this->andWhere('debt_balance.processed_at IS NOT NULL');
    }

    public function amountNotEmpty($alias = 'debt_balance'): self
    {
        return DebtBalance::STORE_EMPTY_AMOUNT ? $this->andWhere("{{{$alias}}}.amount <> 0") : $this;
    }

    /**
     * @param DebtBalance[] $models
     * @param string $operand
     *
     * @return self|ActiveQuery
     */
    public function balances(array $models, string $operand = 'IN'): self
    {
        return $this->models($models, $operand);
    }
}
