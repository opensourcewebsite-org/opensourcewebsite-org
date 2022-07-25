<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%payment_method}}`.
 */
class m210626_081348_add_url_column_to_payment_method_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%payment_method}}', 'url', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%payment_method}}', 'url');
    }
}
