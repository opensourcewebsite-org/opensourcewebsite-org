<?php

namespace app\models;

use app\models\Issue;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * IssueSearch represents the model behind the search form of `app\models\Issue`.
 */
class IssueSearch extends Issue
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'created_at', 'updated_at'], 'integer'],
            [['title', 'description'], 'safe'],
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
        $query = Issue::find()->alias('i')
            ->leftJoin(UserIssueVote::tableName() . ' uiv', 'uiv.issue_id = i.id');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['created_at' => SORT_DESC]],
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
            $query->andFilterWhere(['or', ['like', 'title', $params['table_search']], ['like', 'description', $params['table_search']]]);
        }

        $query->groupBy('i.id');

        return $dataProvider;
    }
}
