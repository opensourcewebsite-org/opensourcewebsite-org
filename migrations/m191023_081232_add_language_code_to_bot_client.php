<?php

use yii\db\Migration;

/**
 * Class m191023_081232_add_language_code_to_bot_client
 */
class m191023_081232_add_language_code_to_bot_client extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%bot_client}}', 'language_code', $this->string(32)->notNull()->defaultValue('en'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%bot_client}}', 'language_code');
    }
}
