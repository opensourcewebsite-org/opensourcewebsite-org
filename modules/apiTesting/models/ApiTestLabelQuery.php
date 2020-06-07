<?php

namespace app\modules\apiTesting\models;

/**
 * This is the ActiveQuery class for [[ApiTestLabel]].
 *
 * @see ApiTestLabel
 */
class ApiTestLabelQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return ApiTestLabel[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return ApiTestLabel|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
