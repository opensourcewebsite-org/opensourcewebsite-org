<?php

declare(strict_types=1);

namespace app\models\search;

use app\models\Vacancy;
use Yii;
use yii\data\ActiveDataProvider;

class VacancySearch extends Vacancy
{
    public int $status = self::STATUS_ON;

    public function rules(): array
    {
        return [
            ['status', 'in', 'range' => [self::STATUS_ON, self::STATUS_OFF]],
            [
                [
                    'name',
                    'max_hourly_rate',
                    'currency_id',
                    'remote_on',
                    'gender_id',
                    'company_id',
                ],
                'safe',
            ],
        ];
    }

    public function search(array $params): ActiveDataProvider
    {
        $query = Vacancy::find()
            ->userOwner();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            $query->where(['0=1']);

            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['status' => $this->status])
            ->andFilterWhere(['remote_on' => $this->remote_on])
            ->andFilterWhere(['max_hourly_rate' => $this->max_hourly_rate])
            ->andFilterWhere(['currency_id' => $this->currency_id])
            ->andFilterWhere(['gender_id' => $this->gender_id])
            ->andFilterWhere(['company_id' => $this->company_id]);

        return $dataProvider;
    }
}
