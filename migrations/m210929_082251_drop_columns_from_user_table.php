<?php

use yii\db\Migration;

/**
 * Handles dropping columns from table `{{%user}}`.
 */
class m210929_082251_drop_columns_from_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('{{%user}}', 'email');
        $this->dropColumn('{{%user}}', 'is_authenticated');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%user}}', 'email', $this->string()->unique()->after('password_reset_token'));
        $this->addColumn('{{%user}}', 'is_authenticated', $this->tinyInteger()->null()->after('email'));
    }
}
