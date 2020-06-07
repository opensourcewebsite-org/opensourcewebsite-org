<?php

namespace app\modules\apiTesting\models;

/**
 * This is the ActiveQuery class for [[ApiTestRequestLabel]].
 *
 * @see ApiTestRequestLabel
 */
class ApiTestRequestLabelQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return ApiTestRequestLabel[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return ApiTestRequestLabel|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    public function byServer($id)
    {
        return $this->joinWith('request')->andWhere(['server_id' => $id]);
    }
}
