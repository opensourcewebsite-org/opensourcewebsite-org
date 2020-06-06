<?php

namespace app\modules\apiTesting\models;

use app\modules\apiTesting\models\ApiTestResponse;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ApiTestResponseSearch represents the model behind the search form of `app\modules\apiTesting\models\ApiTestResponse`.
 */
class ApiTestResponseSearch extends ApiTestResponse
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'request_id', 'code', 'time', 'size', 'created_at'], 'integer'],
            [['headers', 'body', 'cookies'], 'safe'],
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
    public function search($params)
    {
        $query = ApiTestResponse::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if ( ! $this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'request_id' => $this->request_id,
            'code' => $this->code,
            'time' => $this->time,
            'size' => $this->size,
            'created_at' => $this->created_at,
        ]);

        $query->andFilterWhere(['like', 'headers', $this->headers])
            ->andFilterWhere(['like', 'body', $this->body])
            ->andFilterWhere(['like', 'cookies', $this->cookies]);

        return $dataProvider;
    }
}
