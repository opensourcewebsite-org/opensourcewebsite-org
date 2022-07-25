<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%contact_group}}`.
 */
class m200525_060014_add_position_column_to_contact_group_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%contact_group}}', 'position', $this->integer()->unsigned());
        $this->createIndex('{{%idx-contact_group-position}}', '{{%contact_group}}', 'position');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%contact_group}}', 'position');
    }
}
