<?php

use yii\db\Migration;

/**
 * Handles the creation of table `moqup`.
 */
class m180822_083413_create_moqup_table extends Migration {

    /**
     * {@inheritdoc}
     */
    public function safeUp() {
        $this->createTable('moqup', [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer()->notNull()->unsigned(),
            'title' => $this->string()->notNull(),
            'html' => $this->text()->notNull(),
            'created_at' => $this->integer()->unsigned(),
            'updated_at' => $this->integer()->unsigned()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown() {
        $this->dropTable('moqup');
    }

}
