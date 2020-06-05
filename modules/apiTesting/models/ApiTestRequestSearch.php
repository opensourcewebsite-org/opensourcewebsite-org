<?php

namespace app\modules\apiTesting\models;

use app\modules\apiTesting\models\ApiTestRequest;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ApiTestRequestSearch represents the model behind the search form of `app\modules\apiTesting\models\ApiTestRequest`.
 */
class ApiTestRequestSearch extends ApiTestRequest
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'server_id', 'correct_response_code', 'updated_at', 'updated_by'], 'integer'],
            [['name', 'method', 'uri', 'body'], 'safe'],
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
        $query = ApiTestRequest::find();

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
            'server_id' => $this->server_id,
            'correct_response_code' => $this->correct_response_code,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'method', $this->method])
            ->andFilterWhere(['like', 'uri', $this->uri])
            ->andFilterWhere(['like', 'body', $this->body]);

        return $dataProvider;
    }
}
