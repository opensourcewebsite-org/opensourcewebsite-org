<?php

use yii\db\Migration;

/**
 * Handles adding forked_of to table `moqup`.
 */
class m180926_013059_add_forked_of_column_to_moqup_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('moqup', 'forked_of', $this->integer()->unsigned());
        $this->addForeignKey(
            'fk-moqup-forked_of',
            'moqup',
            'forked_of',
            'moqup',
            'id',
            'SET NULL'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-moqup-forked_of',
            'moqup'
        );
        $this->dropColumn('moqup', 'forked_of');
    }
}
