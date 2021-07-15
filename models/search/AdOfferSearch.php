<?php

declare(strict_types=1);

namespace app\models\search;

use Yii;
use app\models\AdOffer;
use yii\data\ActiveDataProvider;

class AdOfferSearch extends AdOffer
{
    public function rules(): array
    {
        return [
            [
                [
                    'id',
                    'currency_id',
                    'status',
                ],
                'integer',
            ],
            ['title', 'string'],
            ['price', 'double'],

        ];
    }

    public function search(array $params): ActiveDataProvider
    {
        $query = AdOffer::find()->where(['user_id' => Yii::$app->user->getIdentity()->getId()]);

        $dataProvider = new ActiveDataProvider(['query' => $query]);

        $this->load($params);

        if (!$this->validate()) {
            $query->where(['0=1']);
            return $dataProvider;
        }

        $query
            ->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['status' => $this->status])
            ->andFilterWhere(['price' => $this->price])
            ->andFilterWhere(['currency_id' => $this->currency_id]);

        return $dataProvider;
    }
}
