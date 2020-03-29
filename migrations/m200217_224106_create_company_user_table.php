<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%company_user}}`.
 */
class m200217_224106_create_company_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%company_user}}', [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'company_id' => $this->integer()->unsigned()->notNull(),
            'user_role' => $this->tinyInteger()->unsigned()->notNull()->defaultValue(0),
        ]);

        $this->addForeignKey(
            'fk-company_user-user_id',
            '{{%company_user}}',
            'user_id',
            '{{%user}}',
            'id'
        );

        $this->addForeignKey(
            'fk-company_user-company_id',
            '{{%company_user}}',
            'company_id',
            '{{%company}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-company_user-user_id',
            '{{%company_user}}'
        );

        $this->dropForeignKey(
            'fk-company_user-company_id',
            '{{%company_user}}'
        );
        $this->dropTable('{{%company_user}}');
    }
}
