<?php

namespace app\interfaces\UserRelation;

/**
 * Interface UserRelationByOwnerInterface
 *
 * Determine relation between 2 users in terms of Ownership
 *
 * @package app\interfaces
 */
interface ByOwnerInterface
{
    /**
     * User who created this model
     * @return mixed
     * @see ByOwnerTrait::getOwnerUID()
     */
    public function getOwnerUID();

    /**
     * @return mixed
     * @see ByOwnerTrait::getLinkedUID()
     */
    public function getLinkedUID();
}
