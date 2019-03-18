<?php

namespace app\models;

use app\models\Issue;
use app\models\WikinewsPage;
use app\models\WikinewsLanguage;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * IssueSearch represents the model behind the search form of `app\models\Issue`.
 */
class WikinewsPageSearch extends WikinewsPage
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id','language_id', 'created_at', ], 'integer'],
            [['title', ], 'safe'],
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
         $query = WikinewsPage::find()
            ->select(['{{%wikinews_page}}.*'])
            ->where(['{{%wikinews_page}}.pageid' => 1])
            ->groupBy('{{%wikinews_page}}.id')
            ->orderBy(['id' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
        ]);

        if (!empty($params['viewYours'])) {
            $query->andFilterWhere(['i.user_id' => Yii::$app->user->identity->id]);
        } else if (!empty($params['viewNew'])) {
            $userVoted = static::getIssuesUserVoted();
            $query->andFilterWhere(['not in', 'i.id', $userVoted]);
        } else if (!empty($params['viewYes'])) {
            $query->andFilterWhere(['uiv.vote_type' => UserIssueVote::YES, 'uiv.user_id' => Yii::$app->user->identity->id]);
        } else if (!empty($params['viewNeutral'])) {
            $query->andFilterWhere(['uiv.vote_type' => UserIssueVote::NEUTRAL, 'uiv.user_id' => Yii::$app->user->identity->id]);
        } else if (!empty($params['viewNo'])) {
            $query->andFilterWhere(['uiv.vote_type' => UserIssueVote::NO, 'uiv.user_id' => Yii::$app->user->identity->id]);
        } else if (!empty($params['table_search'])) {
            $query->andFilterWhere(['or', ['like', 'title', $params['table_search']]]);
        }


        return $dataProvider;
    }
}
