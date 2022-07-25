<?php

use yii\db\Migration;

/**
 * Handles dropping columns from table `{{%contact_group}}`.
 */
class m211004_040726_drop_position_column_from_contact_group_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('{{%contact_group}}', 'position');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%contact_group}}', 'position', $this->integer()->unsigned());
        $this->createIndex('{{%idx-contact_group-position}}', '{{%contact_group}}', 'position');
    }
}
