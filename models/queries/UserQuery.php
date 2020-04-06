<?php

namespace app\models\queries;

use app\models\User;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[\app\models\User]].
 *
 * @see User
 * @method  all() User[]
 * @method  one() User|array|null
 */
class UserQuery extends ActiveQuery
{
    public function active()
    {
        return $this->andWhere([
            'user.status'           => User::STATUS_ACTIVE,
            'user.is_authenticated' => 1,
        ]);
    }
}
