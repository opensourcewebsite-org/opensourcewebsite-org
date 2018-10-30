<?php

namespace app\models\search;

use app\models\Rating;
use app\models\UserWikiPage;
use app\models\WikiPage;
use Yii;
use yii\base\Model;
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
            ->where(['>=', 'amount', 1])
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

    public function searchMissing($params)
    {
        $userId = isset($params['userId']) ? $params['userId'] : 0;
        $languageId = isset($params['languageId']) ? $params['languageId'] : 0;

        $queryGroup = WikiPage::find()
            ->joinWith('users')
            ->select('group_id')
            ->distinct()
            ->where(['{{%user}}.id' => $userId]);

        $queryMissingPages = WikiPage::find()
            ->joinWith('users')
            ->select('{{%wiki_page}}.*')
            ->distinct()
            ->where(['group_id' => $queryGroup])
            ->andWhere([
                'language_id' => $languageId,
                'user_id' => null,
            ]);

        $queryMissingPages->andFilterWhere(['like', 'title', $this->title]);

        $dataProvider = new ActiveDataProvider([
            'query' => $queryMissingPages,
            'sort' => false,
        ]);

        return $dataProvider;
    }
}
