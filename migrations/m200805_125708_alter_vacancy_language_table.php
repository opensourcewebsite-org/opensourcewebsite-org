<?php

use yii\db\Migration;

/**
 * Class m200805_125708_alter_vacancy_language_table
 */
class m200805_125708_alter_vacancy_language_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey(
            'fk-vacancy_language_vacancy_id-vacancy_id',
            'vacancy_language'
        );

        $this->addForeignKey(
            'fk-vacancy_language_vacancy_id-vacancy_id',
            'vacancy_language',
            'vacancy_id',
            'vacancy',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-vacancy_language_vacancy_id-vacancy_id',
            'vacancy_language'
        );

        $this->addForeignKey(
            'fk-vacancy_language_vacancy_id-vacancy_id',
            'vacancy_language',
            'vacancy_id',
            'vacancy',
            'id'
        );
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200805_125708_alter_vacancy_language_table cannot be reverted.\n";

        return false;
    }
    */
}
