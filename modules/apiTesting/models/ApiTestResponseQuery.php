<?php

namespace app\modules\apiTesting\models;

/**
 * This is the ActiveQuery class for [[ApiTestResponse]].
 *
 * @see ApiTestResponse
 */
class ApiTestResponseQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return ApiTestResponse[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return ApiTestResponse|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
