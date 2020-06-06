<?php

namespace app\modules\apiTesting\models;

/**
 * This is the ActiveQuery class for [[ApiTestServerDomain]].
 *
 * @see ApiTestServerDomain
 */
class ApiTestServerDomainQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return ApiTestServerDomain[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return ApiTestServerDomain|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
