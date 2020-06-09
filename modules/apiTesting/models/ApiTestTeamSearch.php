<?php

namespace app\modules\apiTesting\models;

use app\modules\apiTesting\models\ApiTestTeam;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ApiTestTeamSearch represents the model behind the search form of `app\modules\apiTesting\models\ApiTestTeam`.
 */
class ApiTestTeamSearch extends ApiTestTeam
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'project_id', 'invited_at'], 'integer'],
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
        $query = ApiTestTeam::find();

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
            'user_id' => $this->user_id,
            'project_id' => $this->project_id,
            'invited_at' => $this->invited_at,
        ]);

        return $dataProvider;
    }
}
