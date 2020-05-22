<?php

namespace app\models\queries;

use app\interfaces\UserRelation\ByDebtInterface;
use app\interfaces\UserRelation\ByOwnerInterface;
use app\models\DebtRedistribution;
use app\models\queries\traits\SelfSearchTrait;
use Yii;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[DebtRedistribution]].
 *
 * @see DebtRedistribution
 *
 * @method DebtRedistribution[]          all()
 * @method null|array|DebtRedistribution one()
 */
class DebtRedistributionQuery extends ActiveQuery
{
    use SelfSearchTrait;

    public function userOwner($id = null, $method = 'andWhere'): self
    {
        return $this->$method(['debt_redistribution.user_id' => $id ?? Yii::$app->user->id]);
    }

    public function userLinked($id, $method = 'andWhere'): self
    {
        return $this->$method(['debt_redistribution.link_user_id' => $id]);
    }

    /**
     * @param ByOwnerInterface|ByDebtInterface $modelSource
     */
    public function usersByModelSource($modelSource, $method = 'andWhere'): self
    {
        $model = (new DebtRedistribution())->setUsers($modelSource);

        return $this->userOwner($model->user_id, $method)
            ->userLinked($model->link_user_id, $method);
    }

    public function currency($id, $method = 'andWhere'): self
    {
        return $this->$method(['debt_redistribution.currency_id' => $id]);
    }

    public function maxAmount($amount, $method = 'andWhere'): self
    {
        if ($amount === DebtRedistribution::MAX_AMOUNT_ANY) {
            $condition = 'debt_redistribution.max_amount IS NULL';
        } else {
            $condition = ['debt_redistribution.max_amount' => $amount];
        }

        return $this->$method($condition);
    }

    public function maxAmountIsNotDeny($method = 'andWhere'): self
    {
        return $this->$method(
            'debt_redistribution.max_amount IS NULL OR debt_redistribution.max_amount > :drmaDeny',
            [':drmaDeny' => DebtRedistribution::MAX_AMOUNT_DENY]
        );
    }
}
