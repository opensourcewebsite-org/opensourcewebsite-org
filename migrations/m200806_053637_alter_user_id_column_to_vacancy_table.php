<?php

use yii\db\Migration;

/**
 * Class m200806_053637_alter_user_id_column_to_vacancy_table
 */
class m200806_053637_alter_user_id_column_to_vacancy_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('{{vacancy}}', 'user_id', $this->integer()->unsigned()->notNull());

        $this->addForeignKey(
            'fk-vacancy-user_id',
            '{{%vacancy}}',
            'user_id',
            '{{%user}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-vacancy-user_id', '{{%vacancy}}');

        $this->alterColumn('{{vacancy}}', 'user_id', $this->integer()->unsigned());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200806_053637_alter_user_id_column_to_vacancy_table cannot be reverted.\n";

        return false;
    }
    */
}
