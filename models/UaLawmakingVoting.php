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
 * @property int|null $sent
 * @property int|null $message_id
 */
class UaLawmakingVoting extends \yii\db\ActiveRecord
{
    const MIN_ACCEPTED_VOTES = 226;

    /** @var array|null */
    public $laws;

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
            [['event_id', 'for', 'against', 'abstain', 'not_voting', 'total', 'presence', 'absent', 'sent_at', 'message_id'], 'integer'],
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

    /**
     * @return string
     */
    public function getVotingFullLink()
    {
        return '<a href="http://w1.c1.rada.gov.ua/pls/radan_gs09/ns_golos?g_id=' . $this->event_id . '">' . $this->name . '</a>';
    }

    /**
     * @param int $lawId
     *
     * @return string
     */
    public function getLawFullLink($lawId)
    {
        return '<a href="http://w1.c1.rada.gov.ua/pls/zweb2/webproc4_2?pf3516=' . $lawId . '&skl=10">' . $lawId . '</a>';
    }

    /**
     * @return array|null
     */
    public function getLaws()
    {
        if (!is_array($this->laws)) {
            $pattern = '/[(|\s]№([а-яё\d-]+)[,|)]/u';
            preg_match_all($pattern, $this->name, $matches);
            $this->laws = $matches[1];
        }

        return $this->laws;
    }

    /**
     * @return array
     */
    public function getLawsFullLinks()
    {
        $lawsFullLinks = [];

        foreach ($this->laws as $lawId) {
            $lawsFullLinks[] = $this->getLawFullLink($lawId);
        }

        return $lawsFullLinks;
    }

    /**
     * @return boolean
     */
    public function isAccepted()
    {
        return $this->for >= self::MIN_ACCEPTED_VOTES ? true : false;
    }
}
