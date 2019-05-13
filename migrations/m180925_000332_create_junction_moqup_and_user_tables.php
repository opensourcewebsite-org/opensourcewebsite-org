<?php

use yii\db\Migration;

/**
 * Handles the creation of table `moqup_user`.
 * Has foreign keys to the tables:
 *
 * - `moqup`
 * - `user`
 */
class m180925_000332_create_junction_moqup_and_user_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('user_moqup_follow', [
            'moqup_id' => $this->integer()->unsigned(),
            'user_id' => $this->integer()->unsigned(),
            'PRIMARY KEY(moqup_id, user_id)',
        ]);

        // creates index for column `moqup_id`
        $this->createIndex(
            'idx-user_moqup_follow-moqup_id',
            'user_moqup_follow',
            'moqup_id'
        );

        // add foreign key for table `moqup`
        $this->addForeignKey(
            'fk-user_moqup_follow-moqup_id',
            'user_moqup_follow',
            'moqup_id',
            'moqup',
            'id',
            'CASCADE'
        );

        // creates index for column `user_id`
        $this->createIndex(
            'idx-user_moqup_follow-user_id',
            'user_moqup_follow',
            'user_id'
        );

        // add foreign key for table `user`
        $this->addForeignKey(
            'fk-user_moqup_follow-user_id',
            'user_moqup_follow',
            'user_id',
            'user',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `moqup`
        $this->dropForeignKey(
            'fk-user_moqup_follow-moqup_id',
            'user_moqup_follow'
        );

        // drops index for column `moqup_id`
        $this->dropIndex(
            'idx-user_moqup_follow-moqup_id',
            'user_moqup_follow'
        );

        // drops foreign key for table `user`
        $this->dropForeignKey(
            'fk-user_moqup_follow-user_id',
            'user_moqup_follow'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            'idx-user_moqup_follow-user_id',
            'user_moqup_follow'
        );

        $this->dropTable('user_moqup_follow');
    }
}
