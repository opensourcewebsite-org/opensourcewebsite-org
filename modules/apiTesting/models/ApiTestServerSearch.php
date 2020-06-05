<?php

namespace app\modules\apiTesting\models;

use app\modules\apiTesting\models\ApiTestServer;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ApiTestServerSearch represents the model behind the search form of `app\modules\apiTesting\models\ApiTestServer`.
 */
class ApiTestServerSearch extends ApiTestServer
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'project_id', 'created_at', 'updated_at'], 'integer'],
            [['protocol', 'domain', 'path', 'txt'], 'safe'],
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
        $query = ApiTestServer::find();

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
            'project_id' => $this->project_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'protocol', $this->protocol])
            ->andFilterWhere(['like', 'domain', $this->domain])
            ->andFilterWhere(['like', 'path', $this->path])
            ->andFilterWhere(['like', 'txt', $this->txt]);

        return $dataProvider;
    }
}
