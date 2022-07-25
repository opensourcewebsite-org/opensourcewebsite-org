<?php

use yii\db\Migration;

/**
 * Class m180803_123248_add_rating_to_user_table
 */
class m180803_123248_add_rating_to_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'rating', $this->integer()->unsigned()->notNull()->defaultValue(1));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%user}}', 'rating');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180803_123248_add_rating_to_user_table cannot be reverted.\n";

        return false;
    }
    */
}
