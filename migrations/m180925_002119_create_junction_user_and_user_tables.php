<?php

use yii\db\Migration;

/**
 * Handles the creation of table `user_user`.
 * Has foreign keys to the tables:
 *
 * - `user`
 */
class m180925_002119_create_junction_user_and_user_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('user_user_follow', [
            'followed_user_id' => $this->integer()->unsigned(),
            'user_id' => $this->integer()->unsigned(),
            'PRIMARY KEY(followed_user_id, user_id)',
        ]);

        // creates index for column `user_id`
        $this->createIndex(
            'idx-user_user_follow-user_id',
            'user_user_follow',
            'user_id'
        );

        // creates index for column `followed_user_id`
        $this->createIndex(
            'idx-user_user_follow-followed_user_id',
            'user_user_follow',
            'followed_user_id'
        );

        // add foreign key for table `user`
        $this->addForeignKey(
            'fk-user_user_follow-user_id',
            'user_user_follow',
            'user_id',
            'user',
            'id',
            'CASCADE'
        );

        // add foreign key for table `user`
        $this->addForeignKey(
            'fk-user_user_follow-followed_user_id',
            'user_user_follow',
            'followed_user_id',
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
        // drops foreign key for table `user`
        $this->dropForeignKey(
            'fk-user_user_follow-user_id',
            'user_user_follow'
        );
        // drops foreign key for table `user`
        $this->dropForeignKey(
            'fk-user_user_follow-followed_user_id',
            'user_user_follow'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            'idx-user_user_follow-user_id',
            'user_user_follow'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            'idx-user_user_follow-followed_user_id',
            'user_user_follow'
        );

        $this->dropTable('user_user_follow');
    }
}
