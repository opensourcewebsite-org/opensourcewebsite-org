<?php

use yii\db\Migration;

/**
 * Handles dropping columns from table `{{%bot}}`.
 */
class m200208_112813_drop_type_column_from_bot_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('bot', 'type');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('bot', 'type', $this->tinyInteger()->unsigned()->notNull()->defaultValue(0));
    }
}
