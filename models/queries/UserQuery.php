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
        return $this->select('(YEAR(CURDATE()) - YEAR(birthday)) AS age');
    }

    public function statisticYearOfBirth()
    {
        return $this->select('YEAR(birthday) AS year, COUNT(*) AS count')->groupBy('year')->orderBy([
            'count' => SORT_DESC
        ]);
    }
}
