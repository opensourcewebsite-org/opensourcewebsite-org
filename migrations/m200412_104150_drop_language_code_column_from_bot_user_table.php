<?php

use yii\db\Migration;

/**
 * Handles dropping columns from table `{{%bot_user}}`.
 */
class m200412_104150_drop_language_code_column_from_bot_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('{{%bot_user}}', 'language_code');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%bot_user}}', 'language_code', $this->string()->notNull()->defaultValue('en'));
    }
}
