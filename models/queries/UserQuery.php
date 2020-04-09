<?php

namespace app\models\queries;

use app\models\User;
use app\models\UserStatistic;
use yii\db\ActiveQuery;
use yii\db\Expression;

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

    public function authenticated()
    {
        return $this->andWhere(['is_authenticated' => true]);
    }

    public function statisticAge()
    {
        return $this->select('(SUM(CASE WHEN id < 101 THEN 1 ELSE 0 END)) as '.UserStatistic::AGE_JUNIOR)
            ->addSelect('(SUM(CASE WHEN id >= 101 AND id < 103 THEN 1 ELSE 0 END)) as '.UserStatistic::AGE_MIDDLE)
            ->addSelect('(SUM(CASE WHEN id >= 103 THEN 1 ELSE 0 END)) as '.UserStatistic::AGE_SENIOR);
    }
}
