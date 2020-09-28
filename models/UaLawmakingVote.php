<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "bot_ua_lawmaking_vote".
 *
 * @property int $id
 * @property int $message_id
 * @property int $provider_voter_id
 * @property int $vote
 */
class UaLawmakingVote extends \yii\db\ActiveRecord
{
    public const VOTE_LIKE = 1;
    public const VOTE_DISLIKE = -1;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'bot_ua_lawmaking_vote';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['message_id', 'provider_voter_id', 'vote'], 'required'],
            [['message_id', 'provider_voter_id', 'vote'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'message_id' => 'Message ID',
            'provider_voter_id' => 'Provider Voter ID',
            'vote' => 'Vote',
        ];
    }
}
