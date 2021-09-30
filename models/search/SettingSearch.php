<?php

declare(strict_types=1);

namespace app\models\search;

use app\models\Setting;
use yii\data\ActiveDataProvider;
use Yii;

class SettingSearch extends Setting
{
    public function rules(): array
    {
        return [
            [['key', 'value', 'updated_at'], 'safe'],
        ];
    }

    public function search(array $params): ActiveDataProvider
    {
        $query = Setting::find()
         ->where([
            'not', ['value' => null],
        ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> [
                'defaultOrder' => [
                    'updated_at' => SORT_DESC,
                    'key' => SORT_ASC,
                ],
                'enableMultiSort' => true,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            $query->where('0=1');

            return $dataProvider;
        }

        $query->andFilterWhere(['key' => $this->key]);

        return $dataProvider;
    }
}
