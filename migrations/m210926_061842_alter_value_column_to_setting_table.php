<?php

use yii\db\Migration;

/**
 * Class m210926_061842_alter_value_column_to_setting_table
 */
class m210926_061842_alter_value_column_to_setting_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('{{%setting}}', 'value', $this->text());
        $this->alterColumn('{{%setting_value}}', 'value', $this->text()->notNull());
        $this->alterColumn('{{%setting_value_vote}}', 'created_at', $this->integer()->unsigned()->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('{{%setting}}', 'value', $this->text()->notNull());
        $this->alterColumn('{{%setting_value_vote}}', 'created_at', $this->integer()->unsigned());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210926_061842_alter_value_column_to_setting_table cannot be reverted.\n";

        return false;
    }
    */
}
