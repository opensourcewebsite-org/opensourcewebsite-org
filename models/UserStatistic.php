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

    const AGE_JUNIOR = '"<18"';
    const AGE_MIDDLE = '"18-35"';
    const AGE_SENIOR = '"36-60"';
    const AGE_OLD = '>60';

    /**
     * @param string $type
     * @return ArrayDataProvider
     */
    public function get(string $type)
    {
        switch ($type) {
            case self::AGE:
                return $this->ageStatistics();
                break;
            default:
                break;
        }
    }

    /**
     *
     * @return ArrayDataProvider
     */
    public  function ageStatistics()
    {
        $statistics = User::find()->active()->statisticAge()->asArray()->all();

        return new ArrayDataProvider([
            'allModels' => $this->prepareAgeModels($statistics[0]),
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'attributes' => ['id', 'count'],
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
        $counter = 0;
        foreach ($data as $age => $count) {
            $result[] = [
                'id' => ++$counter,
                'age' => $age,
                'count' => $count,
            ];
        }
        return $result;
    }
}
