<?php

use yii\db\Migration;

/**
 * Class m190118_130541_update_support_group_client_table
 */
class m190118_130541_update_support_group_client_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('support_group_client', 'language_code', $this->string(255));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('support_group_client', 'language_code', $this->string(255)->notNull());
    }
}
