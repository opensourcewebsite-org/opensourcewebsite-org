<?php

namespace app\modules\bot\models;

use Yii;

/**
 * This is the model class for table "bot_voteban_voting".
 *
 * @property int $id
 * @property int $provider_starter_id
 * @property int $provider_candidate_id
 * @property int $chat_id
 * @property int $voting_message_id
 * @property int $candidate_message_id
 */
class VotebanVoting extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'bot_voteban_voting';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['provider_starter_id', 'provider_candidate_id', 'chat_id', 'voting_message_id', 'candidate_message_id'], 'required'],
            [['provider_starter_id', 'provider_candidate_id', 'chat_id', 'voting_message_id', 'candidate_message_id'], 'integer'],
            [['provider_candidate_id', 'chat_id', 'voting_message_id'], 'unique', 'targetAttribute' => ['provider_candidate_id', 'chat_id', 'voting_message_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'provider_starter_id' => 'Provider Starter ID',
            'provider_candidate_id' => 'Provider Candidate ID',
            'chat_id' => 'Chat ID',
            'voting_message_id' => 'Voting Message ID',
            'candidate_message_id' => 'Candidate Message ID',
        ];
    }
}
