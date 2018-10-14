<?php

namespace app\models\search;

use Yii;
use yii\base\Model;
use app\models\WikiPage;
use app\models\UserWikiPage;
use app\models\Rating;
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
        $allUsers = isset($params['allUsers']) ? $params['allUsers'] : false;

        $subQueryRatingId = Rating::find()
            ->select('(MAX(id))')
            ->groupBy('user_id');

        $subQueryUsersId = Rating::find()
            ->select('user_id')
            ->distinct(true)
            ->where(['>', 'balance', 0])
            ->andWhere(['id' => $subQueryRatingId]);

        $query = WikiPage::find()
            ->select(['{{%wiki_page}}.*'])
            ->joinWith('users')
            ->andWhere(['{{%wiki_page}}.language_id' => $this->language_id])
            ->andWhere(['{{%user}}.id' => $subQueryUsersId])
            ->groupBy('{{%wiki_page}}.id')
            ->orderBy(['title' => SORT_ASC]);

        if ($this->type === null && !$allUsers) {
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
