<?php

namespace app\models\search;

use app\models\CronJob;
use Yii;
use app\models\CronJobLog;
use yii\data\ActiveDataProvider;

/**
 * Class CronJobSearch
 *
 * @package app\models\search
 */
class CronJobSearch extends CronJobLog
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['message', 'cron_job_id'], 'integer'],
            [['message'], 'string'],
        ];
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

        $query = self::find()->with('cronJob');

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'sort'       => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ],
            ],
            'pagination' => [
                'pageSize' => 25,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'cron_job_id' => $this->cron_job_id,
        ]);

        $query->andFilterWhere(
            [
                'like', 'message', $this->message,
            ]
        );

        return $dataProvider;
    }
}
