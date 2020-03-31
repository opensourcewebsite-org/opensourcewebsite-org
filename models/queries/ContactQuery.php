<?php

namespace app\models\queries;

use app\models\Contact;
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
    public function virtual(bool $isVirtual)
    {
        if ($isVirtual) {
            $this->andWhere(['contact.link_user_id' => null]);
        } else {
            $this->andWhere(['IS NOT', 'contact.link_user_id',  null]);
        }

        return $this;
    }

    public function userOwner($id = null)
    {
        return $this->andWhere(['contact.user_id' => $id ?? Yii::$app->user->id]);
    }
}
