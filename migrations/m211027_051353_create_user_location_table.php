<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user_location}}`.
 */
class m211027_051353_create_user_location_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%user_location}}', [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'location_lat' => $this->string()->notNull(),
            'location_lon' => $this->string()->notNull(),
            'updated_at' => $this->integer()->unsigned()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-user_location-user_id',
            '{{%user_location}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-user_location-user_id',
            '{{%user_location}}'
        );

        $this->dropTable('{{%user_location}}');
    }
}
