<?php

use yii\db\Migration;

/**
 * Handles the creation of table `rating`.
 * Has foreign keys to the tables:
 *
 * - `user`
 */
class m181007_154209_create_rating_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('rating', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->unsigned(),
            'balance' => $this->integer()->notNull(),
            'amount' => $this->integer()->notNull(),
            'type' => $this->smallinteger()->unsigned()->notNull(),
            'created_at' => $this->integer()->notNull(),
        ]);

        // creates index for column `user_id`
        $this->createIndex(
            'idx-rating-user_id',
            'rating',
            'user_id'
        );

        // add foreign key for table `user`
        $this->addForeignKey(
            'fk-rating-user_id',
            'rating',
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
        // drops foreign key for table `user`
        $this->dropForeignKey(
            'fk-rating-user_id',
            'rating'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            'idx-rating-user_id',
            'rating'
        );

        $this->dropTable('rating');
    }
}
