<?php

use yii\db\Migration;

/**
 * Class m211001_131019_alter_contact_table
 */
class m211001_131019_alter_contact_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update(
            'contact',
            ['vote_delegation_priority' => 0],
            ['vote_delegation_priority' => null]
        );

        $this->alterColumn('{{%contact}}', 'vote_delegation_priority', $this->tinyInteger()->unsigned()->notNull()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('{{%contact}}', 'vote_delegation_priority', $this->tinyInteger()->unsigned()->defaultValue(null));
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m211001_131019_alter_contact_table cannot be reverted.\n";

        return false;
    }
    */
}
