<?php

namespace app\models;

use yii\data\ArrayDataProvider;

/**
 * Class UserStatistic
 *
 * Find statistic data for User model.
 * @package app\models
 */
class UserStatistic
{
    const AGE = 'age';
    const YEAR_OF_BIRTH = 'year_of_birth';
    const GENDER = 'gender';
    const SEXUALITY = 'sexuality';
    const CURRENCY = 'currency';
    const INTERFACE_LANGUAGE = 'interface_language';
    const LANGUAGE_AND_LEVEL = 'language_level';

    /**
     * @param string $type
     * @return ArrayDataProvider
     */
    public function get(string $type)
    {
        switch ($type) {
            case self::AGE:
                return $this->age();
                break;
            case self::YEAR_OF_BIRTH:
                return $this->yearOfBirth();
                break;
            case self::GENDER:
                return $this->gender();
                break;
            case self::SEXUALITY:
                return $this->sexuality();
                break;
            case self::CURRENCY:
                return $this->currency();
                break;
            case self::INTERFACE_LANGUAGE:
                return $this->interfaceLanguage();
                break;
            case self::LANGUAGE_AND_LEVEL:
                return $this->languageAndLevel();
                break;
            default:
                break;
        }
    }

    /**
     * @return ArrayDataProvider
     */
    protected function age()
    {
        $models = User::find()
            ->active()
            ->statisticAge()
            ->asArray()
            ->all();

        $uniqueCount = array_count_values(array_column($models, 'age'));

        return new ArrayDataProvider([
            'allModels' => $this->prepareAgeModels($uniqueCount),
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'attributes' => ['count', 'age'],
            ],
        ]);
    }

    /**
     * Prepare result array.
     *
     * @param $data
     * @return array
     */
    protected function prepareAgeModels($data)
    {
        $result = [];
        foreach ($data as $age => $count) {
            $result[] = [
                'age' => $age,
                'count' => $count,
            ];
        }
        return $result;
    }

    /**
     * @return ArrayDataProvider
     */
    protected function yearOfBirth()
    {
        $models = User::find()
            ->active()
            ->statisticYearOfBirth()
            ->asArray()
            ->all();

        return new ArrayDataProvider([
            'allModels' => $models,
            'sort' => [
                'attributes' => ['year', 'count'],
                'defaultOrder' => [
                    'count' => SORT_DESC,
                ],
            ],
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);
    }

    /**
     * @return ArrayDataProvider
     */
    protected function gender()
    {
        $models = User::find()
            ->addSelect('g.name as gender')
            ->addSelect('(CASE WHEN g.name IS NULL THEN "Not specified" ELSE g.name END) as gender')
            ->join('left join', 'gender g', 'user.gender_id=g.id')
            ->asArray()
            ->all();

        $uniqueCount = array_count_values(array_column($models, 'gender'));

        return new ArrayDataProvider([
            'allModels' => $this->prepareGenderModels($uniqueCount),
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'attributes' => ['count', 'gender'],
            ],
        ]);
    }

    /**
     * @param $data
     * @return array
     */
    protected function prepareGenderModels($data)
    {
        $result = [];
        foreach ($data as $gender => $count) {
            $result[] = [
                'gender' => $gender,
                'count' => $count,
            ];
        }
        return $result;
    }

    /**
     * @return ArrayDataProvider
     */
    protected function sexuality()
    {
        $models = User::find()
            ->addSelect('(CASE WHEN s.name IS NULL THEN "Not specified" ELSE s.name END) as sexuality')
            ->join('left join', 'sexuality s', 'user.sexuality_id=s.id')
            ->asArray()
            ->all();

        $uniqueCount = array_count_values(array_column($models, 'sexuality'));

        return new ArrayDataProvider([
            'allModels' => $this->prepareSexualityModels($uniqueCount),
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'attributes' => ['count', 'sexuality'],
            ],
        ]);
    }

    /**
     * @param $data
     * @return array
     */
    protected function prepareSexualityModels($data)
    {
        $result = [];
        foreach ($data as $sexuality => $count) {
            $result[] = [
                'sexuality' => $sexuality,
                'count' => $count,
            ];
        }
        return $result;
    }

    /**
     * @return ArrayDataProvider
     */
    protected function currency()
    {
        $models = User::find()
            ->addSelect('(CASE WHEN c.code IS NULL THEN "Not specified" ELSE c.code END) AS currency')
            ->join('left join', 'currency c', 'user.currency_id=c.id')
            ->asArray()
            ->all();

        $uniqueCount = array_count_values(array_column($models, 'currency'));

        return new ArrayDataProvider([
            'allModels' => $this->prepareCurrencyModels($uniqueCount),
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'attributes' => ['count', 'currency'],
            ],
        ]);
    }

    /**
     * @param $data
     * @return array
     */
    protected function prepareCurrencyModels($data)
    {
        $result = [];
        foreach ($data as $currency => $count) {
            $result[] = [
                'currency' => $currency,
                'count' => $count,
            ];
        }
        return $result;
    }

    protected function interfaceLanguage()
    {
        $models = User::find()
            ->addSelect('l.name as lang, count(*) as count')
            ->join('inner join', 'bot_user b', 'b.user_id=user.id')
            ->join('inner join', 'language l', 'l.id=b.language_id')
            ->groupBy('lang')
            ->orderBy('count DESC')
            ->asArray()
            ->all();

        return new ArrayDataProvider([
            'allModels' => $models,
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'attributes' => ['count', 'currency'],
            ],
        ]);
    }

    /**
     * @return ArrayDataProvider
     */
    protected function languageAndLevel()
    {
        $models = User::find()
            ->addSelect('l.name as lang, lev.description as level, count(*) as count')
            ->join('inner join', 'user_language ul', 'ul.user_id=user.id')
            ->join('inner join', 'language l', 'l.id=ul.language_id')
            ->join('inner join', 'language_level lev', 'lev.id=ul.language_level_id')
            ->groupBy('level, lang')
            ->orderBy('count DESC')
            ->asArray()
            ->all();

        $levels = $this->langLevels();
        $levels = array_merge(['lang'], $levels);
        $keys = array_fill_keys($levels, null);

        $result = [];
        foreach ($models as $model) {
            [$lang, $level, $count] = array_values($model);

            if (! array_key_exists($lang, $result)) {
                $result[$lang] = $keys;
                $result[$lang]['lang'] = $lang;
            }

            if (! array_key_exists($level, $result[$lang])) {
                $result[$lang][$level] = $count;
            } else {
                $result[$lang][$level] += $count;
            }
        }

        return new ArrayDataProvider([
            'allModels' => $result,
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'attributes' => ['count', 'currency'],
            ],
        ]);
    }

    /**
     * @return array
     */
    private function langLevels(): array
    {
        $levels = LanguageLevel::find()
            ->select('description')
            ->distinct()
            ->asArray()
            ->all();

        return array_column($levels, 'description');
    }
}

