<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user_language}}`.
 */
class m200330_130521_create_user_language_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%user_language}}', [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'language_id' => $this->integer()->unsigned()->notNull(),
            'language_level_id' => $this->integer()->unsigned()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-user_language_user_id-user_id',
            '{{%user_language}}',
            'user_id',
            '{{%user}}',
            'id'
        );

        $this->addForeignKey(
            'fk-user_language_language_id-language_id',
            '{{%user_language}}',
            'language_id',
            '{{%language}}',
            'id'
        );

        $this->addForeignKey(
            'fk-user_language_language_level_id-language_level_id',
            '{{%user_language}}',
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

        $this->dropForeignKey(
            'fk-user_language_user_id-user_id',
            '{{%user_language}}'
        );

        $this->dropForeignKey(
            'fk-user_language_language_id-language_id',
            '{{%user_language}}'
        );

        $this->dropForeignKey(
            'fk-user_language_language_level_id-language_level_id',
            '{{%user_language}}'
        );

        $this->dropTable('{{%user_language}}');
    }
}
