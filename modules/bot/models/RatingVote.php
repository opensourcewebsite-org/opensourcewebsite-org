<?php

namespace app\modules\bot\models;

use Yii;

/**
 * This is the model class for table "bot_rating_vote".
 *
 * @property int $id
 * @property int $chat_id
 * @property int $message_id
 * @property int $provider_candidate_id
 * @property int $provider_voter_id
 * @property int $vote
 */
class RatingVote extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'bot_rating_vote';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['chat_id', 'message_id', 'provider_candidate_id', 'provider_voter_id', 'vote'], 'required'],
            [['chat_id', 'message_id', 'provider_candidate_id', 'provider_voter_id', 'vote'], 'integer'],
            [['chat_id', 'message_id', 'provider_voter_id'], 'unique', 'targetAttribute' => ['chat_id', 'message_id', 'provider_voter_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'chat_id' => 'Chat ID',
            'message_id' => 'Message ID',
            'provider_candidate_id' => 'Provider Candidate ID',
            'provider_voter_id' => 'Provider Voter ID',
            'vote' => 'Vote',
        ];
    }
}
