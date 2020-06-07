<?php

namespace app\modules\apiTesting\models;

/**
 * This is the ActiveQuery class for [[ApiTestServer]].
 *
 * @see ApiTestServer
 */
class ApiTestServerQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return ApiTestServer[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return ApiTestServer|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    public function verified()
    {
        return $this->joinWith('domain')->andWhere(['status' => ApiTestServer::STATUS_VERIFIED]);
    }

    public function unverified()
    {
        return $this->joinWith('domain')->andWhere(['status' => ApiTestServer::STATUS_VERIFICATION_PROGRESS]);
    }

    public function expired()
    {
        return $this->joinWith('domain')->andWhere(['status' => ApiTestServer::STATUS_EXPIRED]);
    }
}
