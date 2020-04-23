<?php

namespace app\interfaces\UserRelation;

/**
 * Interface UserRelationByDebtInterface
 *
 * Determine relation between 2 users in terms of Debt
 *
 * @package app\interfaces
 */
interface ByDebtInterface
{
    /**
     * This user should return money to {@see getDebtReceiverUID()}
     * @return mixed
     * @see ByDebtTrait::getDebtorUID()
     */
    public function getDebtorUID();

    /**
     * This user will receive money from {@see getDebtorUID()}
     * @return mixed
     * @see ByDebtTrait::getDebtReceiverUID()
     */
    public function getDebtReceiverUID();
}
