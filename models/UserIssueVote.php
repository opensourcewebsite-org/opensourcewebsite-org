<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user_issue_vote".
 *
 * @property int $id
 * @property int $user_id
 * @property int $issue_id
 * @property int $vote_type
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Issue $issue
 * @property User $user
 */
class UserIssueVote extends \yii\db\ActiveRecord
{
    public const YES = 1;
    public const NO = 3;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_issue_vote';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'issue_id', 'vote_type'], 'required'],
            [['user_id', 'issue_id', 'vote_type', 'created_at', 'updated_at'], 'integer'],
            [['issue_id'], 'exist', 'skipOnError' => true, 'targetClass' => Issue::className(), 'targetAttribute' => ['issue_id' => 'id']],
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
            'issue_id' => 'Issue ID',
            'vote_type' => 'Vote Type',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
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
    public function getIssue()
    {
        return $this->hasOne(Issue::className(), ['id' => 'issue_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return integer The number in percentage
     */
    public function getVotesPercent()
    {
        return $this->user->getRatingPercent(false);
    }

    /**
     * @param integer $issueId the issue ID
     * @param integer $userId the user ID
     * @return integer vote type
     */
    public static function getUserVoteType($issueId = '', $userId = '')
    {
        return static::find()->select('vote_type')->where(['issue_id' => $issueId, 'user_id' => $userId])->scalar();
    }

    /**
     * @param integer $issueId the issue ID
     * @param bool $excludeCurrentUser whether to exclude login user id
     * @return integer total number of vote count for issue
     */
    public static function getIssueVoteCount($issueId = '', $excludeCurrentUser = false)
    {
        $voteCount = static::find()->where(['issue_id' => $issueId]);
        if ($excludeCurrentUser) {
            $voteCount->andWhere(['!=', 'user_id', Yii::$app->user->id]);
        }
        return $voteCount->count();
    }
}
