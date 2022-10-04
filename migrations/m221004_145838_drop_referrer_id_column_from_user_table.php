<?php

use yii\db\Migration;

/**
 * Handles dropping columns from table `{{%user}}`.
 */
class m221004_145838_drop_referrer_id_column_from_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('{{%fk-user-referrer_id}}', '{{%user}}');

        $this->dropColumn('{{%user}}', 'referrer_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%user}}', 'referrer_id', $this->integer()->unsigned()->after('status'));

        $this->createIndex(
            '{{%idx-user-referrer_id}}',
            '{{%user}}',
            'referrer_id'
        );

        $this->addForeignKey(
            '{{%fk-user-referrer_id}}',
            '{{%user}}',
            'referrer_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );
    }
}
