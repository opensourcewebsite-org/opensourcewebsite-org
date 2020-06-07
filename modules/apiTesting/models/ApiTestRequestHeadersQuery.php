<?php

namespace app\modules\apiTesting\models;

/**
 * This is the ActiveQuery class for [[ApiTestRequestHeaders]].
 *
 * @see ApiTestRequestHeaders
 */
class ApiTestRequestHeadersQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return ApiTestRequestHeaders[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return ApiTestRequestHeaders|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
