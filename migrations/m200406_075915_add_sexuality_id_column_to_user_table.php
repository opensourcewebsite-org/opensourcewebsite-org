<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%user}}`.
 */
class m200406_075915_add_sexuality_id_column_to_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'sexuality_id', $this->integer()->unsigned());

        $this->addForeignKey(
            'fk-user_sexuality_id-sexuality_id',
            '{{%user}}',
            'sexuality_id',
            '{{%sexuality}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-user_sexuality_id-sexuality_id',
            '{{%user}}'
        );

        $this->dropColumn('{{%user}}', 'sexuality_id');
    }
}
