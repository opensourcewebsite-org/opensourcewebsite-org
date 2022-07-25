<?php

declare(strict_types=1);

namespace app\models\search;

use app\models\CurrencyExchangeOrder;
use Yii;
use yii\data\ActiveDataProvider;

class CurrencyExchangeOrderSearch extends CurrencyExchangeOrder
{
    public int $status = self::STATUS_ON;

    public function rules(): array
    {
        return [
            ['status', 'in', 'range' => [self::STATUS_ON, self::STATUS_OFF]],
            [
                [
                    'selling_currency_label',
                    'buying_currency_label',
                    'selling_currency_id',
                    'buying_currency_id',
                    'selling_currency_min_amount',
                    'selling_currency_max_amount',
                ],
                'safe',
            ],
        ];
    }

    public function search(array $params): ActiveDataProvider
    {
        $query = CurrencyExchangeOrder::find()
            ->userOwner();

        $dataProvider = new ActiveDataProvider(['query' => $query]);

        $this->load($params);

        if (!$this->validate()) {
            $query->where(['0=1']);

            return $dataProvider;
        }

        $query->andFilterWhere(['status' => $this->status])
            ->andFilterWhere(['like', 'selling_currency_label', $this->selling_currency_label])
            ->andFilterWhere(['like', 'buying_currency_label', $this->buying_currency_label])
            ->andFilterWhere(['selling_currency_id' => $this->selling_currency_id])
            ->andFilterWhere(['buying_currency_id' => $this->buying_currency_id])
            ->andFilterWhere(['selling_currency_min_amount' => $this->selling_currency_min_amount])
            ->andFilterWhere(['selling_currency_max_amount' => $this->selling_currency_max_amount]);

        return $dataProvider;
    }
}
