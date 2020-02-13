<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%bot_client}}`.
 */
class m200204_170427_add_user_id_column_to_bot_client_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%bot_client}}', 'user_id', $this->integer()->unsigned());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%bot_client}}', 'user_id');
    }
}
