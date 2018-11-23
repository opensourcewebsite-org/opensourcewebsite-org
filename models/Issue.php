<?php

namespace app\models;

use app\components\Converter;
use Yii;

/**
 * This is the model class for table "issue".
 *
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $description
 * @property int $created_at
 * @property int $updated_at
 *
 * @property User $user
 * @property UserIssueVote[] $userIssueVotes
 */
class Issue extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'issue';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'title', 'description'], 'required'],
            [['user_id', 'created_at', 'updated_at'], 'integer'],
            [['description'], 'string'],
            [['title'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'title' => 'Title',
            'description' => 'Description',
            'created_at' => 'Created at',
            'updated_at' => 'Updated at',
        ];
    }

    /**
     * Make some changes before the record is saved
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $this->updated_at = time();

        return true;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserIssueVotes()
    {
        return $this->hasMany(UserIssueVote::className(), ['issue_id' => 'id']);
    }

    /**
     * @param bool $format whether to return formatted percent value or not
     * @return array Votes percentage of an issue by vote type
     */
    public function getUserVotesPercent($format = true)
    {
        $issueVotes = $this->userIssueVotes;
        $votes = [1 => 0, 2 => 0, 3 => 0];
        foreach ($issueVotes as $issueVote) {
            $votes[$issueVote->vote_type] += $issueVote->getVotesPercent();
        }
        if ($format) {
            foreach ($votes as $i => $vote) {
                $votes[$i] = Converter::formatNumber($vote);
            }
        }
        return $votes;
    }

    /**
     * @return integer selected vote type by login user for current issue
     */
    public function getUserVoteSelected()
    {
        $issue_id = $this->id;
        $user_id = Yii::$app->user->id;
        return UserIssueVote::getUserVoteType($issue_id, $user_id);
    }

    /**
     * @return Issue the issues for which login user has already voted
     */
    public static function getIssuesUserVoted()
    {
        return static::find()
            ->alias('i')
            ->select(['i.id'])
            ->leftJoin(UserIssueVote::tableName() . ' uiv', 'uiv.issue_id = i.id')
            ->andWhere(['uiv.user_id' => Yii::$app->user->identity->id]);
    }

    /**
     * @return integer the issues count for which login user has not voted i.e. new issues
     */
    public static function getNewIssuesCount()
    {
        $userVoted = static::getIssuesUserVoted();
        return static::find()->alias('i')->where(['not in', 'id', $userVoted])->count();
    }

    /**
     * @return IssueVote votes of users other than the creator
     */
    public static function hasIssuesVoteOfOthers($issueId)
    {
        return UserIssueVote::find()
            ->andWhere(['issue_id' => $issueId])
            ->andWhere(['NOT IN', 'user_id', Yii::$app->user->identity->id])
            ->exists();
    }
}
