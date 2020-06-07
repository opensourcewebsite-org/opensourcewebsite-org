<?php

namespace app\modules\apiTesting\models;

use app\modules\apiTesting\models\ApiTestRunner;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ApiTestRunnerSearch represents the model behind the search form of `app\modules\apiTesting\models\ApiTestRunner`.
 */
class ApiTestRunnerSearch extends ApiTestRunner
{
    public $project_id;
    public $server_id;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [[
                'id',
                'job_id',
                'request_id',
                'triggered_by',
                'triggered_by_schedule',
                'timing',
                'status',
                'start_at',
                'project_id',
                'server_id'
            ], 'integer'],
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
        $query = ApiTestRunner::find();

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
            'job_id' => $this->job_id,
            'request_id' => $this->request_id,
            'triggered_by' => $this->triggered_by,
            'triggered_by_schedule' => $this->triggered_by_schedule,
            'timing' => $this->timing,
            'status' => $this->status,
            'start_at' => $this->start_at,
        ])
            ->byProject($this->project_id)
            ->byServer($this->server_id);

        return $dataProvider;
    }
}
