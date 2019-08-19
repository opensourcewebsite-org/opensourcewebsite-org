<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Moqup;

/**
 * MoqupSearch represents the model behind the search form of `app\models\Moqup`.
 */
class MoqupSearch extends Moqup
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'created_at', 'updated_at'], 'integer'],
            [['title', 'html'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
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
        $query = Moqup::find()
            ->alias('m')
            ->leftJoin(UserMoqupFollow::tableName() . ' umf', 'umf.moqup_id = m.id');

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> ['defaultOrder' => ['updated_at'=>SORT_DESC]]
        ]);

        $this->load($params);

        if (isset($params['viewYours']) && $params['viewYours']) {
            $query->andFilterWhere(['m.user_id' => Yii::$app->user->identity->id]);
        } else if (isset($params['viewFollowing']) && $params['viewFollowing']) {
            $query->andFilterWhere(['umf.user_id' => Yii::$app->user->identity->id]);
        }

        if (!$this->validate()) {
            return $dataProvider;
        }

        return $dataProvider;
    }
}
