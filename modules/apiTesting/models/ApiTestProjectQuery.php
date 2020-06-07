<?php

namespace app\modules\apiTesting\models;

/**
 * This is the ActiveQuery class for [[ApiTestProject]].
 *
 * @see ApiTestProject
 */
class ApiTestProjectQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return ApiTestProject[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return ApiTestProject|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    public function my()
    {
        return $this
            ->joinWith('teams t')
            ->orWhere([
                'type' => ApiTestProject::PROJECT_TYPE_PUBLIC
            ])
            ->orWhere([
                'type' => ApiTestProject::PROJECT_TYPE_PRIVATE,
                't.user_id' => \Yii::$app->user->id
            ]);
    }
}
