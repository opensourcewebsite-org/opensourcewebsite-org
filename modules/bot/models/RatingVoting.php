<?php

namespace app\modules\bot\models;

use Yii;

/**
 * This is the model class for table "bot_rating_voting".
 *
 * @property int $id
 * @property int $chat_id
 * @property int $candidate_message_id
 * @property int $voting_message_id
 */
class RatingVoting extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'bot_rating_voting';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['chat_id', 'candidate_message_id', 'voting_message_id'], 'required'],
            [['chat_id', 'candidate_message_id', 'voting_message_id'], 'integer'],
            [['chat_id', 'candidate_message_id', 'voting_message_id'], 'unique', 'targetAttribute' => ['chat_id', 'candidate_message_id', 'voting_message_id']],
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
            'candidate_message_id' => 'Candidate Message ID',
            'voting_message_id' => 'Voting Message ID',
        ];
    }
}
