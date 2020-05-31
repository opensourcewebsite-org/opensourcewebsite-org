<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%vacancy_language}}`.
 */
class m200412_103747_create_vacancy_language_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%vacancy_language}}', [
            'id' => $this->primaryKey()->unsigned(),
            'vacancy_id' => $this->integer()->unsigned()->notNull(),
            'language_id' => $this->integer()->unsigned()->notNull(),
            'language_level_id' => $this->integer()->unsigned()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-vacancy_language_vacancy_id-vacancy_id',
            '{{%vacancy_language}}',
            'vacancy_id',
            '{{%vacancy}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-vacancy_language_language_id-language_id',
            '{{%vacancy_language}}',
            'language_id',
            '{{%language}}',
            'id'
        );

        $this->addForeignKey(
            'fk-vacancy_language_language_level_id-language_level_id',
            '{{%vacancy_language}}',
            'language_level_id',
            '{{%language_level}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%vacancy_language}}');
    }
}
