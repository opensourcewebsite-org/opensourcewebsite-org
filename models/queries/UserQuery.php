<?php

declare(strict_types=1);

namespace app\models\queries;

use app\models\queries\traits\RandomTrait;
use app\models\User;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[\app\models\User]].
 *
 * @see User
 * @method User[]          all()
 * @method null|array|User one()
 */
class UserQuery extends ActiveQuery
{
    use RandomTrait;

    /**
     * @return self
     */
    public function active()
    {
        return $this->andWhere([
            User::tableName() . '.status' => User::STATUS_ACTIVE,
        ]);
    }

    /**
     * @return self
     */
    public function age()
    {
        return $this->select('(YEAR(CURDATE()) - YEAR(birthday)) AS age, COUNT(*) AS users')
            ->andHaving(['not', ['age' => null]])
            ->groupBy('age')
            ->orderBy([
                'users' => SORT_DESC,
            ]);
    }

    /**
     * @return self
     */
    public function statisticYearOfBirth()
    {
        return $this->select('YEAR(birthday) AS year, COUNT(*) AS users')
            ->andHaving(['not', ['year' => null]])
            ->groupBy('year')
            ->orderBy([
                'users' => SORT_DESC,
            ]);
    }

    /**
     * @return self
     */
    public function gender()
    {
        return $this->select('g.name AS gender')
            ->addSelect('g.name as gender')
            ->addSelect('COUNT(*) AS users')
            ->join('LEFT JOIN', 'gender g', 'user.gender_id=g.id')
            ->groupBy('gender')
            ->having(['not', ['gender' => null]])
            ->orderBy([
               'users' => SORT_DESC,
            ]);
    }

    /**
     * @return self
     */
    public function sexuality()
    {
        return $this->select('s.name AS sexuality')
            ->addSelect('COUNT(*) AS users')
            ->join('LEFT JOIN', 'sexuality s', 'user.sexuality_id=s.id')
            ->having(['not', ['sexuality' => null]])
            ->groupBy('sexuality')
            ->orderBy([
                'users' => SORT_DESC,
            ]);
    }

    /**
     * @return self
     */
    public function currency()
    {
        return $this
            ->select('c.code AS currency')
            ->addSelect('COUNT(*) AS users')
            ->join('LEFT JOIN', 'currency c', 'user.currency_id=c.id')
            ->having(['not', ['currency' => null]])
            ->groupBy('currency')
            ->orderBy([
                'users' => SORT_DESC,
            ]);
    }

    /**
     * @return self
     */
    public function interfaceLanguage()
    {
        return $this->addSelect('l.name_ascii as lang, count(*) as users')
            ->join('inner join', 'bot_user b', 'b.user_id=user.id')
            ->join('inner join', 'language l', 'l.id=b.language_id')
            ->groupBy('lang')
            ->orderBy([
                'users' => SORT_DESC,
            ]);
    }

    /**
     * @return self
     */
    public function languageAndLevel()
    {
        return $this->addSelect('l.name_ascii as lang, lev.description as level, count(*) as users')
            ->join('inner join', 'user_language ul', 'ul.user_id=user.id')
            ->join('inner join', 'language l', 'l.id=ul.language_id')
            ->join('inner join', 'language_level lev', 'lev.id=ul.language_level_id')
            ->groupBy('level, lang')
            ->orderBy([
                'users' => SORT_DESC,
            ]);
    }

    /**
     * @return self
     */
    public function citizenship()
    {
        return $this->select('country.name as country, count(*) as users')
            ->join('inner join', 'user_citizenship uc', 'uc.user_id=user.id')
            ->join('inner join', 'country', 'country.id=uc.country_id')
            ->groupBy('country')
            ->orderBy([
                'users' => SORT_DESC,
            ]);
    }
}
