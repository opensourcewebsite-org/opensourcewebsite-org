<?php

declare(strict_types=1);

namespace app\models\search;

use app\models\Resume;
use yii\data\ActiveDataProvider;
use Yii;

class ResumeSearch extends Resume
{
    public int $status = Resume::STATUS_ON;

    public function rules(): array
    {
        return [
            ['status', 'in', 'range' => [Resume::STATUS_ON, Resume::STATUS_OFF]],
            [['name', 'min_hourly_rate', 'currency_id'], 'safe'],
        ];
    }

    public function search(array $params): ActiveDataProvider
    {
        $query = Resume::find()->where(['user_id' => Yii::$app->user->identity->id]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);

        $this->load($params);

        if (!$this->validate()) {
            $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere(['status' => $this->status]);
        $query->andFilterWhere(['like','name',$this->name]);
        $query->andFilterWhere(['min_hourly_rate' => $this->min_hourly_rate]);
        $query->andFilterWhere(['currency_id' => $this->currency_id]);

        return $dataProvider;
    }
}
