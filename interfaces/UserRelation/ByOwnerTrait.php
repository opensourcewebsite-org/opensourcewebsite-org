<?php

namespace app\interfaces\UserRelation;

use app\models\queries\UserQuery;
use app\models\User;
use yii\db\ActiveQuery;

/**
 * Trait ByOwnerTrait
 *
 * @property User $ownerUser
 * @property User $linkedUser
 */
trait ByOwnerTrait
{
    use UserRelationTrait;

    /**
     * @return mixed
     * @see ByOwnerInterface::ownerUID()
     */
    public function ownerUID($value = null)
    {
        if (isset($value)) {
            $this->user_id = $value;
        }

        return $this->user_id;
    }

    /**
     * @return mixed
     * @see ByOwnerInterface::linkedUID()
     */
    public function linkedUID($value = null)
    {
        if (isset($value)) {
            $this->link_user_id = $value;
        }

        return $this->link_user_id;
    }

    /**
     * @see ByOwnerInterface::getOwnerAttribute()
     */
    public static function getOwnerAttribute(): string
    {
        return 'user_id';
    }

    /**
     * @see ByOwnerInterface::getLinkedAttribute()
     */
    public static function getLinkedAttribute(): string
    {
        return 'link_user_id';
    }

    /**
     * @return UserQuery|ActiveQuery
     */
    public function getOwnerUser()
    {
        return $this->hasOne(User::className(), ['id' => self::getOwnerAttribute()]);
    }

    /**
     * @return UserQuery|ActiveQuery
     */
    public function getLinkedUser()
    {
        return $this->hasOne(User::className(), ['id' => self::getLinkedAttribute()]);
    }
}
