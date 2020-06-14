<?php

namespace app\components\helpers;

use app\models\Debt;
use app\models\DebtBalance;
use app\models\DebtRedistribution;
use yii\base\Component;

class DebtHelper extends Component
{
    /**
     * @param $depositPending Pending deposit
     * @param $depositConfirmed Confirmed deposit
     */
    public static function getDepositAmount($depositPending, $depositConfirmed)
    {
        $amount = $depositConfirmed;
        if (!empty($depositPending)) {
            $amount = $depositConfirmed . ' (' . $depositPending . ')';
        }

        return $amount;
    }

    /**
     * @param $creditPending Pending credit
     * @param $creditConfirmed Confirmed credit
     */
    public static function getCreditAmount($creditPending, $creditConfirmed)
    {
        $amount = $creditConfirmed;
        if (!empty($creditPending)) {
            $amount = $creditConfirmed . ' (' . $creditPending . ')';
        }

        return $amount;
    }
}
