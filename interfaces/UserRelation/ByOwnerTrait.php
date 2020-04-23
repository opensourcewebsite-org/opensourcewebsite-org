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
    /**
     * @return mixed
     * @see ByOwnerInterface::getOwnerUID()
     */
    public function getOwnerUID()
    {
        return $this->user_id;
    }

    /**
     * @return mixed
     * @see ByOwnerInterface::getLinkedUID()
     */
    public function getLinkedUID()
    {
        return $this->link_user_id;
    }

    /**
     * @return UserQuery|ActiveQuery
     */
    public function getOwnerUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return UserQuery|ActiveQuery
     */
    public function getLinkedUser()
    {
        return $this->hasOne(User::className(), ['id' => 'link_user_id']);
    }
}
