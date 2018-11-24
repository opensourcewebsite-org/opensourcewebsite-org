<?php

use yii\db\Migration;
use app\models\UserIssueVote;

/**
 * Class m181124_123859_upgrade_user_issue_vote_table
 */
class m181124_123859_upgrade_user_issue_vote_table extends Migration
{
    const NEUTRAL = 2;
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        UserIssueVote::deleteAll(['vote_type' => self::NEUTRAL]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181124_123859_upgrade_user_issue_vote_table cannot be reverted.\n";

        return false;
    }
}
