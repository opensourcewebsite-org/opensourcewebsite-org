<?php

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
    public function groupCondition($group): self
    {
        return $this->andWhere(['debt.group' => $group]);
    }
}
