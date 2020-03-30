<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user_citizenship}}`.
 */
class m200330_171406_create_user_citizenship_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%user_citizenship}}', [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer()->unsigned(),
            'country_id' => $this->integer()->unsigned(),
        ]);

        $this->addForeignKey(
            'fk-user_citizenship_user_id-user_id',
            '{{%user_citizenship}}',
            'user_id',
            '{{%user}}',
            'id'
        );

        $this->addForeignKey(
            'fk-user_citizenship_country_id-country_id',
            '{{user_citizenship}}',
            'country_id',
            '{{%country}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-user_citizenship_user_id-user_id',
            '{{%user_citizenship}}'
        );

        $this->dropForeignKey(
            'fk-user_citizenship_country_id-country_id',
            '{{user_citizenship}}'
        );

        $this->dropTable('{{%user_citizenship}}');
    }
}
