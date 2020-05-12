<?php

namespace app\models\queries;

use app\models\Currency;
use app\models\queries\traits\RandomTrait;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[Currency]].
 *
 * @see Currency
 *
 * @method Currency[]          all()
 * @method null|array|Currency one()
 */
class CurrencyQuery extends ActiveQuery
{
    use RandomTrait;

    /**
     * @param int      $fromUserId
     * @param int      $toUserId
     * @param int|null $modelId     specify it on Update form (to exclude all except this one)
     *
     * @return self
     */
    public function excludeExistedInDebtRedistribution($fromUserId, $toUserId, $modelId = null): self
    {
        $condition = ['debt_redistribution.id' => null];
        if ($modelId) {
            $condition = ['OR', $condition, ['debt_redistribution.id' => $modelId]];
        }

        return $this
            ->joinWith([
                'debtRedistributions' => function (DebtRedistributionQuery $query) use ($fromUserId, $toUserId) {
                    $query->fromUser($fromUserId, 'andOnCondition');
                    $query->toUser($toUserId, 'andOnCondition');
                },
            ])
            ->andWhere($condition);
    }

    public function code($code): self
    {
        return $this->andWhere(['currency.code' => $code]);
    }
}
