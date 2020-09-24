<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "rada_vote".
 *
 * @property int $id
 * @property int $id_event
 * @property string $date_event
 * @property string $name
 * @property int $for
 * @property int $against
 * @property int $abstain
 * @property int $not_voting
 * @property int $absent
 */
class RadaVote extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'rada_vote';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_event', 'date_event', 'name', 'for', 'against', 'abstain', 'not_voting', 'absent'], 'required'],
            [['id_event', 'for', 'against', 'abstain', 'not_voting', 'absent'], 'integer'],
            [['date_event'], 'safe'],
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
            'id_event' => 'Id Event',
            'date_event' => 'Date Event',
            'name' => 'Name',
            'for' => 'For',
            'against' => 'Against',
            'abstain' => 'Abstain',
            'not_voting' => 'Not Voting',
            'absent' => 'Absent',
        ];
    }
}
