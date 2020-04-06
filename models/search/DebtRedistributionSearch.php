<?php

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\DebtRedistribution;
use yii\data\Sort;

/**
 * DebtRedistributionSearch represents the model behind the search form of `app\models\DebtRedistribution`.
 */
class DebtRedistributionSearch extends DebtRedistribution
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'from_user_id', 'to_user_id', 'currency_id'], 'integer', 'min' => 1],
            [['max_amount'], 'number', 'min' => 0],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($toUserId, $params)
    {
        $sort = new Sort([
            'attributes'   => [
                'max_amount',
                'currency_id' => [
                    'asc'     => ['currency.code' => SORT_ASC],
                    'desc'    => ['currency.code' => SORT_DESC],
                    'default' => SORT_ASC,
                ],
            ],
            'defaultOrder' => ['currency_id' => SORT_ASC],
        ]);
        $query = DebtRedistribution::find()
            ->joinWith('currency')
            ->fromUser()
            ->toUser($toUserId)
            ->orderBy($sort->orders);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'sort'       => $sort,
            'pagination' => false,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'currency_id' => $this->currency_id,
            'max_amount'  => $this->max_amount,
        ]);

        return $dataProvider;
    }
}
