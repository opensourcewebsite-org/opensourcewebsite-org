<?php

use yii\db\Migration;

/**
 * Class m191109_074111_add_currency_code_to_bot_client
 */
class m191109_074111_add_currency_code_to_bot_client extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%bot_client}}', 'currency_code', $this->string()->notNull()->defaultValue('USD'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%bot_client}}', 'currency_code');
    }
}
