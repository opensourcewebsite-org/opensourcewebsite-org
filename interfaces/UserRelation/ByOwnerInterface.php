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
     * @see ByOwnerTrait::ownerUID()
     */
    public function ownerUID($value = null);

    /**
     * @return mixed
     * @see ByOwnerTrait::linkedUID()
     */
    public function linkedUID($value = null);

    /**
     * @see ByOwnerTrait::getOwnerAttribute()
     */
    public static function getOwnerAttribute(): string;

    /**
     * @see ByOwnerTrait::getLinkedAttribute()
     */
    public static function getLinkedAttribute(): string;
}
