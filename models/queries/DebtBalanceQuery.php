<?php

namespace app\models\queries;

use app\models\Debt;
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
}
