<?php

namespace app\modules\apiTesting\models;

use yii\db\Expression;

/**
 * This is the ActiveQuery class for [[ApiTestRunner]].
 *
 * @see ApiTestRunner
 */
class ApiTestRunnerQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return ApiTestRunner[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return ApiTestRunner|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    public function byProject($id)
    {
        return
            $this->joinWith(['request r', 'job j', 'request.server s'])
                ->andFilterWhere([
                    'OR',
                    ['j.project_id' => $id],
                    ['s.project_id' => $id]
                ]);
    }

    public function byServer($id)
    {
        return $this->joinWith(['request r', 'job j', 'request.server s'])->andFilterWhere([
            's.id' => $id
        ]);
    }

    public function byStatus($status)
    {
        return $this->andWhere([ApiTestRunner::tableName().'.status' => $status]);
    }

    public function lastPeriod($interval)
    {
        return $this->andWhere([
            '>',
            'from_unixtime(start_at,\'%Y-%m-%d\')',
            new Expression('DATE_SUB(NOW(), INTERVAL '.$interval.')')
        ]);
    }
}
