<?php

namespace app\models\queries;

use app\interfaces\UserRelation\ByDebtInterface;
use app\interfaces\UserRelation\ByOwnerInterface;
use app\models\DebtRedistribution;
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
}
