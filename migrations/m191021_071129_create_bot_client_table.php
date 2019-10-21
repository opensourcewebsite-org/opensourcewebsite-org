<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%bot_client}}`.
 */
class m191021_071129_create_bot_client_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%bot_client}}', [
            'id' => $this->primaryKey()->unsigned(),
            'provider_user_id' => $this->integer()->unsigned()->notNull(),
            'provider_user_name' => $this->string(255),
            'provider_user_blocked' => $this->tinyInteger()->unsigned()->defaultValue(0)->notNull(),
            'location_lat' => $this->string(255),
            'location_lon' => $this->string(255),
            'location_at' => $this->integer()->unsigned(),
            'provider_user_first_name' => $this->string(255),
            'provider_user_last_name' => $this->string(255),
            'last_message_at' => $this->integer()->unsigned(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%bot_client}}');
    }
}
