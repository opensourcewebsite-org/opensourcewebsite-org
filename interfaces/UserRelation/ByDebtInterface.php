<?php

namespace app\interfaces\UserRelation;

/**
 * Interface UserRelationByDebtInterface
 *
 * Determine relation between 2 users in terms of Debt
 *
 * @property int $currency_id
 */
interface ByDebtInterface
{
    /**
     * This user should return money to {@see debtReceiverUID()}
     *
     * @return mixed
     * @see ByDebtTrait::debtorUID()
     */
    public function debtorUID($value = null);

    /**
     * This user will receive money from {@see debtorUID()}
     *
     * @return mixed
     * @see ByDebtTrait::debtReceiverUID()
     */
    public function debtReceiverUID($value = null);

    /**
     * @see ByDebtTrait::getDebtorAttribute()
     */
    public static function getDebtorAttribute(): string;

    /**
     * @see ByDebtTrait::getDebtReceiverAttribute()
     */
    public static function getDebtReceiverAttribute(): string;
}
