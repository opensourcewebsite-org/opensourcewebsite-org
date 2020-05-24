<?php

use yii\db\Migration;

/**
 * Class m200420_163341_update_debt_redistribution_switch_users
 */
class m200420_163341_update_debt_redistribution_rename_users extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn('debt_redistribution', 'from_user_id', 'user_id');
        $this->renameColumn('debt_redistribution', 'to_user_id', 'link_user_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->renameColumn('debt_redistribution', 'user_id', 'from_user_id');
        $this->renameColumn('debt_redistribution', 'link_user_id', 'to_user_id');
    }
}
