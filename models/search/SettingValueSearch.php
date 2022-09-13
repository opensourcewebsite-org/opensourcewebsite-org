<?php

declare(strict_types=1);

namespace app\models\search;

use app\models\Setting;
use app\models\SettingValue;
use Yii;
use yii\data\ActiveDataProvider;

class SettingValueSearch extends SettingValue
{
    public function rules(): array
    {
        return [
            [['setting_id', 'value'], 'safe'],
        ];
    }

    public function search(array $params): ActiveDataProvider
    {
        $setting_id = null;

        if (isset($params['key'])) {
            $setting = Setting::findOne(['key' => $params['key']]);

            if ($setting) {
                $setting_id = $setting->id;
            }
        }

        $query = SettingValue::findByCondition(['setting_id' => $setting_id]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> [
                'defaultOrder' => [
                    'value' => SORT_ASC,
                ],
                'enableMultiSort' => true,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            $query->where('0=1');

            return $dataProvider;
        }

        $query->andFilterWhere(['value' => $this->value]);

        return $dataProvider;
    }
}
