<?php

namespace app\models\traits;

use app\models\DebtBalance;

trait RelationToDebtBalanceTrait
{
    public function getDebtBalance(): DebtBalance
    {
        return $this->debtBalanceDirectionSame ?: $this->debtBalanceDirectionBack;
    }

    public function populateDebtBalance(DebtBalance $balance): void
    {
        if ($this->isDebtBalanceHasSameDirection($balance)) {
            $this->populateRelation('debtBalanceDirectionSame', $balance);
        } else {
            $this->populateRelation('debtBalanceDirectionBack', $balance);
        }
    }

    public function isDebtBalancePopulated(): bool
    {
        return $this->isRelationPopulated('debtBalanceDirectionSame')
            || $this->isRelationPopulated('debtBalanceDirectionBack');
    }

    /**
     * Is Debt's direction (Credit|Deposit) == DebtBalance's direction
     *
     * @param DebtBalance $balance
     *
     * @return bool
     */
    public function isDebtBalanceHasSameDirection(DebtBalance $balance): bool
    {
        foreach ($this->getDebtBalanceDirectionSame()->link as $attributeBalance => $attributeDebt) {
            if ((string)$balance->getAttribute($attributeBalance) !== (string)$this->getAttribute($attributeDebt)) {
                return false;
            }
        }

        return true;
    }
}
