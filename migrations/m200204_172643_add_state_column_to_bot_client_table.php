<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%bot_client}}`.
 */
class m200204_172643_add_state_column_to_bot_client_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%bot_client}}', 'state', $this->json());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%bot_client}}', 'state');
    }
}
