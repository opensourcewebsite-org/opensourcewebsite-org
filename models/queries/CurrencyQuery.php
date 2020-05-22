<?php

namespace app\models\queries;

use app\interfaces\UserRelation\ByDebtInterface;
use app\interfaces\UserRelation\ByOwnerInterface;
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
     * @param ByOwnerInterface|ByDebtInterface $modelSource
     * @param int|null $modelId specify it on Update form (to exclude all except this one)
     *
     * @return self
     */
    public function excludeExistedInDebtRedistribution($modelSource, $modelId = null): self
    {
        $condition = ['debt_redistribution.id' => null];
        if ($modelId) {
            $condition = ['OR', $condition, ['debt_redistribution.id' => $modelId]];
        }

        return $this
            ->joinWith([
                'debtRedistributions' => function (DebtRedistributionQuery $query) use ($modelSource) {
                    $query->usersByModelSource($modelSource, 'andOnCondition');
                },
            ])
            ->andWhere($condition);
    }

    public function code($code): self
    {
        return $this->andWhere(['currency.code' => $code]);
    }
}
