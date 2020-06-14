<?php

namespace app\components\helpers;

use app\models\Debt;
use app\models\DebtBalance;
use app\models\DebtRedistribution;
use yii\base\Component;

class DebtHelper extends Component
{
    /**
     * @param string $amountPending Pending deposit
     * @param string $amountConfirmed Confirmed deposit
     */
    public static function renderAmount($amountPending, $amountConfirmed)
    {
        $amount = $amountConfirmed;
        if ($amountPending) {
            $amount = $amountConfirmed . ' (' . $amountPending . ')';
        }

        return $amount;
    }

    public static function getFloatScale(): int
    {
        return max(
            Debt::getAttributeFloatScale('amount'),
            DebtBalance::getAttributeFloatScale('amount'),
            DebtRedistribution::getAttributeFloatScale('max_amount')
        );
    }
}
