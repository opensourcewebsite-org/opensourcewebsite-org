<?php

namespace app\models\queries;

use app\models\Contact;
use app\models\queries\traits\RandomTrait;
use Yii;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[Contact]].
 *
 * @see Contact
 *
 * @method  all() Contact[]
 * @method  one() Contact|array|null
 */
class ContactQuery extends ActiveQuery
{
    use RandomTrait;

    public function virtual(bool $isVirtual, $method = 'andWhere'): self
    {
        if ($isVirtual) {
            $this->$method(['contact.link_user_id' => null]);
        } else {
            $this->$method(['IS NOT', 'contact.link_user_id', null]);
        }

        return $this;
    }

    public function userOwner($id = null, $method = 'andWhere'): self
    {
        return $this->$method(['contact.user_id' => $id ?? Yii::$app->user->id]);
    }

    public function forDebtRedistribution($contactId): self
    {
        return $this
            ->where(['id' => $contactId])
            ->userOwner()
            ->virtual(false);
    }
}
