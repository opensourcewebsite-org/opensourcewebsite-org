<?php

declare(strict_types=1);

namespace app\models\queries;

use app\models\Debt;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[Debt]].
 *
 * @see Debt
 *
 * @method Debt[]          all()
 * @method null|array|Debt one()
 */
class DebtQuery extends ActiveQuery
{
    /**
     * @return self
     */
    public function groupCondition($group, string $operand = 'IN'): self
    {
        return $this->andWhere([$operand, 'debt.group', $group]);
    }
}
