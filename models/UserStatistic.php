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
}
