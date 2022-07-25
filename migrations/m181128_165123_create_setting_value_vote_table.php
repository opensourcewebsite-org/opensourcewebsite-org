<?php

use yii\db\Migration;

/**
 * Handles the creation of table `setting_value_vote`.
 * Has foreign keys to the tables:
 *
 * - `user`
 * - `setting_value`
 */
class m181128_165123_create_setting_value_vote_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('setting_value_vote', [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'setting_value_id' => $this->integer()->unsigned()->notNull(),
            'setting_id' => $this->integer()->unsigned()->notNull(),
            'created_at' => $this->integer()->unsigned(),
        ]);

        // creates index for column `user_id`
        $this->createIndex(
            'idx-setting_value_vote-user_id',
            'setting_value_vote',
            'user_id'
        );

        // add foreign key for table `user`
        $this->addForeignKey(
            'fk-setting_value_vote-user_id',
            'setting_value_vote',
            'user_id',
            'user',
            'id',
            'CASCADE'
        );

        // creates index for column `setting_value_id`
        $this->createIndex(
            'idx-setting_value_vote-setting_value_id',
            'setting_value_vote',
            'setting_value_id'
        );

        // add foreign key for table `setting_value`
        $this->addForeignKey(
            'fk-setting_value_vote-setting_value_id',
            'setting_value_vote',
            'setting_value_id',
            'setting_value',
            'id',
            'CASCADE'
        );

        // creates index for column `setting_id`
        $this->createIndex(
            'idx-setting_value_vote-setting_id',
            'setting_value_vote',
            'setting_id'
        );

        // add foreign key for table `setting`
        $this->addForeignKey(
            'fk-setting_value_vote-setting_id',
            'setting_value_vote',
            'setting_id',
            'setting',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `user`
        $this->dropForeignKey(
            'fk-setting_value_vote-user_id',
            'setting_value_vote'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            'idx-setting_value_vote-user_id',
            'setting_value_vote'
        );

        // drops foreign key for table `setting_value`
        $this->dropForeignKey(
            'fk-setting_value_vote-setting_value_id',
            'setting_value_vote'
        );

        // drops index for column `setting_value_id`
        $this->dropIndex(
            'idx-setting_value_vote-setting_value_id',
            'setting_value_vote'
        );

        $this->dropTable('setting_value_vote');
    }
}
