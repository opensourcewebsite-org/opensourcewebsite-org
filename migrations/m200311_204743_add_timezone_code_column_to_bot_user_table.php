<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%bot_user}}`.
 */
class m200311_204743_add_timezone_code_column_to_bot_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%bot_user}}', 'timezone_code', $this->integer()->unsigned()->defaultValue(425)->after('currency_code')->notNull());

        $this->addForeignKey(
            'fk-timezone_code-bot_user',
            '{{%bot_user}}',
            'timezone_code',
            '{{%timezone}}',
            'code'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-timezone_code-bot_user');

        $this->dropColumn('{{%bot_user}}', 'timezone_code');
    }
}
