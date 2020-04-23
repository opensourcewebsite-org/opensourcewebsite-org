<?php

namespace app\interfaces\UserRelation;

use app\models\queries\UserQuery;
use app\models\User;
use yii\db\ActiveQuery;

/**
 * Trait ByDebtTrait
 *
 * @property User $toUser
 * @property User $fromUser
 */
trait ByDebtTrait
{
    /**
     * @return mixed
     * @see ByDebtInterface::getDebtorUID()
     */
    public function getDebtorUID()
    {
        return $this->from_user_id;
    }

    /**
     * @return mixed
     * @see ByDebtInterface::getDebtReceiverUID()
     */
    public function getDebtReceiverUID()
    {
        return $this->to_user_id;
    }

    /**
     * @return ActiveQuery|UserQuery
     */
    public function getFromUser()
    {
        return $this->hasOne(User::className(), ['id' => 'from_user_id']);
    }

    /**
     * @return ActiveQuery|UserQuery
     */
    public function getToUser()
    {
        return $this->hasOne(User::className(), ['id' => 'to_user_id']);
    }
}
