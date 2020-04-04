<?php

namespace app\models\queries;

use app\models\DebtRedistribution;
use Yii;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[DebtRedistribution]].
 *
 * @see DebtRedistribution
 *
 * @method  all() DebtRedistribution[]
 * @method  one() DebtRedistribution|array|null
 */
class DebtRedistributionQuery extends ActiveQuery
{
    /**
     * @return self
     */
    public function fromUser($id = null, $method = 'andWhere')
    {
        return $this->$method(['debt_redistribution.from_user_id' => $id ?? Yii::$app->user->id]);
    }

    /**
     * @return self
     */
    public function toUser($id, $method = 'andWhere')
    {
        return $this->$method(['debt_redistribution.to_user_id' => $id]);
    }
}
