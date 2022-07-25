<?php

declare(strict_types=1);

namespace app\models\search;

use app\models\Resume;
use yii\data\ActiveDataProvider;
use Yii;

class ResumeSearch extends Resume
{
    public int $status = self::STATUS_ON;

    public function rules(): array
    {
        return [
            ['status', 'in', 'range' => [self::STATUS_ON, self::STATUS_OFF]],
            [
                [
                    'name',
                    'min_hourly_rate',
                    'currency_id',
                    'remote_on',
                ],
                'safe',
            ],
        ];
    }

    public function search(array $params): ActiveDataProvider
    {
        $query = Resume::find()
            ->userOwner();

        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);

        $this->load($params);

        if (!$this->validate()) {
            $query->where('0=1');

            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['status' => $this->status])
            ->andFilterWhere(['remote_on' => $this->remote_on])
            ->andFilterWhere(['min_hourly_rate' => $this->min_hourly_rate])
            ->andFilterWhere(['currency_id' => $this->currency_id]);

        return $dataProvider;
    }
}
