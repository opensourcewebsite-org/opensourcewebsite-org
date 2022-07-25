<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%contact}}`.
 */
class m200414_131747_add_columns_to_contact_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%contact}}', 'is_real', $this->tinyInteger()->unsigned()->defaultValue(0)->notNull());
        $this->addColumn('{{%contact}}', 'vote_delegation_priority', $this->tinyInteger()->unsigned()->defaultValue(null));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%contact}}', 'is_real');
        $this->dropColumn('{{%contact}}', 'vote_delegation_priority');
    }
}
