<?php

namespace app\models\search;

use app\models\WikinewsPage;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class WikinewsSearch extends WikinewsPage
{
    public function rules()
    {
        return [
            ['language_id', 'integer'],
            [['title'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * @param $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = WikinewsPage::find()
            ->joinWith('language')
            ->where('pageid IS NOT NULL')
            ->groupBy('{{%wikinews_page}}.id')
            ->orderBy(['title' => SORT_ASC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
            'pagination' => [ 'pageSize' => 10 ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'title', $this->title]);

        return $dataProvider;
    }
}
