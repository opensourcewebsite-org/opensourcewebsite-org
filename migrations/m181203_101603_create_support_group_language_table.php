<?php

use yii\db\Migration;

/**
 * Handles the creation of table `support_group_language`.
 */
class m181203_101603_create_support_group_language_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('support_group_language', [
            'id' => $this->primaryKey()->unsigned(),
            'support_group_id' => $this->integer()->unsigned()->notNull(),
            'language_code' => $this->string()->notNull(),
        ]);

        $this->createIndex(
            'idx-support_group_language-language_code',
            'support_group_language',
            'language_code'
        );

        $this->addForeignKey(
            'fk-support_group_language-language_code',
            'support_group_language',
            'language_code',
            'language',
            'code',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-support_group_language-language_code',
            'support_group_language'
        );

        $this->dropIndex(
            'idx-support_group_language-language_code',
            'support_group_language'
        );

        $this->dropTable('support_group_language');
    }
}
