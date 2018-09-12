<?php

namespace app\models\search;

use Yii;
use yii\base\Model;
use app\models\WikiPage;
use app\models\UserWikiPage;
use yii\data\ActiveDataProvider;

class WikiPageSearch extends WikiPage
{

    const TYPE_ALL = 'all';
    const TYPE_RECOMMENDED = 'recommended';

    public $type;

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
        $query = WikiPage::find()
            ->select(['{{%wiki_page}}.*', 'SUM({{%user}}.rating) AS rating'])
            ->joinWith('users')
            ->andWhere(['{{%wiki_page}}.language_id' => $this->language_id])
            ->groupBy('{{%wiki_page}}.id')
            ->having(['>', 'rating', 0])
            ->orderBy(['rating' => SORT_DESC, 'title' => SORT_ASC]);


        if ($this->type === null) {
            $query->andWhere(['{{%user_wiki_page}}.user_id' => Yii::$app->user->id]);
        } elseif ($this->type == self::TYPE_RECOMMENDED) {
            $query->andWhere([
                'not in', '{{%wiki_page}}.id',
                    UserWikiPage::find()
                    ->select('wiki_page_id')
                    ->where(['{{%user_wiki_page}}.user_id' => Yii::$app->user->id]),
            ]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'title', $this->title]);

        return $dataProvider;
    }
}
