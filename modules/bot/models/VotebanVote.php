<?php

namespace app\modules\bot\models;

use Yii;

/**
 * This is the model class for table "bot_voteban_vote".
 *
 * @property int $id
 * @property int $provider_voter_id
 * @property int $provider_candidate_id
 * @property int $chat_id
 * @property int $vote
 */
class VotebanVote extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'bot_voteban_vote';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['provider_voter_id', 'provider_candidate_id', 'chat_id', 'vote'], 'required'],
            [['provider_voter_id', 'provider_candidate_id', 'chat_id', 'vote'], 'integer'],
            [['provider_voter_id', 'provider_candidate_id', 'chat_id'], 'unique', 'targetAttribute' => ['provider_voter_id', 'provider_candidate_id', 'chat_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'provider_voter_id' => 'Provider Voter ID',
            'provider_candidate_id' => 'Provider Candidate ID',
            'chat_id' => 'Chat ID',
            'vote' => 'Vote',
        ];
    }
}
