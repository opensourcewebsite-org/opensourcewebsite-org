<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "bot_ua_lawmaking_voting".
 *
 * @property int $id
 * @property int $event_id
 * @property string $date
 * @property string $name
 * @property int $for
 * @property int $against
 * @property int $abstain
 * @property int $not_voting
 * @property int $total
 * @property int $presence
 * @property int $absent
 */
class UaLawmakingVoting extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'bot_ua_lawmaking_voting';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['event_id', 'date', 'name', 'for', 'against', 'abstain', 'not_voting', 'total', 'presence', 'absent'], 'required'],
            [['event_id', 'for', 'against', 'abstain', 'not_voting', 'total', 'presence', 'absent'], 'integer'],
            [['date'], 'safe'],
            [['name'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'event_id' => 'Event ID',
            'date' => 'Date',
            'name' => 'Name',
            'for' => 'For',
            'against' => 'Against',
            'abstain' => 'Abstain',
            'not_voting' => 'Not Voting',
            'total' => 'Total',
            'presence' => 'Presence',
            'absent' => 'Absent',
        ];
    }
}
