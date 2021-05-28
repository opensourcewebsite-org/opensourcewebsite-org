<?php

use yii\db\Migration;

/**
 * Class m210527_124658_alter_vacancy_change_currency_id_to_nullable
 */
class m210527_124658_alter_vacancy_change_currency_id_to_nullable extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('{{%vacancy}}', 'currency_id', $this->integer()->unsigned());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('{{%vacancy}}', 'currency_id', $this->integer()->unsigned()->notNull());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210527_124658_alter_vacancy_change_currency_id_to_nullable cannot be reverted.\n";

        return false;
    }
    */
}
