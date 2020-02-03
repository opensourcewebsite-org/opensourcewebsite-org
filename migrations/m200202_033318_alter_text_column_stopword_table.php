<?php

use yii\db\Migration;

/**
 * Class m200202_033318_alter_text_column_stopword_table
 */
class m200202_033318_alter_text_column_stopword_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('group_stopwords', 'text', 'string');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('group_stopwords', 'text', 'integer');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200202_033318_alter_text_column_stopword_table cannot be reverted.\n";

        return false;
    }
    */
}
