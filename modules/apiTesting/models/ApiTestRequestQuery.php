<?php

namespace app\modules\apiTesting\models;

/**
 * This is the ActiveQuery class for [[ApiTestRequest]].
 *
 * @see ApiTestRequest
 */
class ApiTestRequestQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return ApiTestRequest[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return ApiTestRequest|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
