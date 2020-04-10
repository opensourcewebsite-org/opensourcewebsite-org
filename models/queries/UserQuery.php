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
    /**
     * @return UserQuery
     */
    public function active()
    {
        return $this->andWhere([
            'user.status'           => User::STATUS_ACTIVE,
            'user.is_authenticated' => 1,
        ]);
    }

    /**
     * @return UserQuery
     */
    public function authenticated()
    {
        return $this->andWhere(['is_authenticated' => true]);
    }

    /**
     * @return UserQuery
     */
    public function age()
    {
        return $this->select('(YEAR(CURDATE()) - YEAR(birthday)) AS age, COUNT(*) AS count')
            ->andHaving(['not', ['age' => null]])
            ->groupBy('age')
            ->orderBy('count DESC');
    }

    /**
     * @return UserQuery
     */
    public function statisticYearOfBirth()
    {
        return $this->select('YEAR(birthday) AS year, COUNT(*) AS count')
            ->andHaving(['not', ['year' => null]])
            ->groupBy('year')
            ->orderBy([
                'count' => SORT_DESC
            ]);
    }

    /**
     * @return UserQuery
     */
    public function gender()
    {
       return $this->select('g.name AS gender')
           ->addSelect('(CASE WHEN g.name IS NULL THEN "Not specified" ELSE g.name END) as gender')
           ->addSelect('COUNT(*) AS count')
           ->join('LEFT JOIN', 'gender g', 'user.gender_id=g.id')
           ->groupBy('gender')
           ->orderBy([
               'count' => SORT_DESC
           ]);
    }

    /**
     * @return UserQuery
     */
    public function sexuality()
    {
        return $this->select('(CASE WHEN s.name IS NULL THEN "Not specified" ELSE s.name END) AS sexuality')
            ->addSelect('COUNT(*) AS count')
            ->join('LEFT JOIN', 'sexuality s', 'user.sexuality_id=s.id')
            ->groupBy('sexuality')
            ->orderBy([
                'count' => SORT_DESC
            ]);
    }

    /**
     * @return UserQuery
     */
    public function currency()
    {
        return $this->select('(CASE WHEN c.code IS NULL THEN "Not specified" ELSE c.code END) AS currency')
            ->addSelect('COUNT(*) AS count')
            ->join('LEFT JOIN', 'currency c', 'user.currency_id=c.id')
            ->groupBy('currency')
            ->orderBy([
                'count' => SORT_DESC
            ]);
    }

    /**
     * @return UserQuery
     */
    public function interfaceLanguage()
    {
        return $this->addSelect('l.name as lang, count(*) as count')
            ->join('inner join', 'bot_user b', 'b.user_id=user.id')
            ->join('inner join', 'language l', 'l.id=b.language_id')
            ->groupBy('lang')
            ->orderBy([
                'count' => SORT_DESC
            ]);
    }

    /**
     * @return UserQuery
     */
    public function languageAndLevel()
    {
        return $this->addSelect('l.name as lang, lev.description as level, count(*) as count')
            ->join('inner join', 'user_language ul', 'ul.user_id=user.id')
            ->join('inner join', 'language l', 'l.id=ul.language_id')
            ->join('inner join', 'language_level lev', 'lev.id=ul.language_level_id')
            ->groupBy('level, lang')
            ->orderBy([
                'count' => SORT_DESC
            ]);
    }

    public function citizenship()
    {
        return $this->select('country.name as country, count(*) as count')
            ->join('inner join', 'user_citizenship uc', 'uc.user_id=user.id')
            ->join('inner join', 'country', 'country.id=uc.country_id')
            ->groupBy('country')
            ->orderBy([
                'count' => SORT_DESC
            ]);
    }
}
