<?php

declare(strict_types=1);

namespace app\models\search;

use app\models\AdSearch;
use Yii;
use yii\data\ActiveDataProvider;

class AdSearchSearch extends AdSearch
{
    public int $status = self::STATUS_ON;

    public function rules(): array
    {
        return [
            ['status', 'in', 'range' => [self::STATUS_ON, self::STATUS_OFF]],
            [['id', 'currency_id'], 'integer'],
            ['title', 'string'],
            ['max_price', 'double'],

        ];
    }

    public function search(array $params): ActiveDataProvider
    {
        $query = AdSearch::find()
            ->userOwner();

        $dataProvider = new ActiveDataProvider(['query' => $query]);

        $this->load($params);

        if (!$this->validate()) {
            $query->where(['0=1']);
            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['status' => $this->status])
            ->andFilterWhere(['max_price' => $this->max_price])
            ->andFilterWhere(['currency_id' => $this->currency_id]);

        return $dataProvider;
    }
}
