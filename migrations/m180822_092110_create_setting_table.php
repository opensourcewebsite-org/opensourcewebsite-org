<?php

use yii\db\Migration;

/**
 * Handles the creation of table `setting`.
 */
class m180822_092110_create_setting_table extends Migration {

    /**
     * {@inheritdoc}
     */
    public function safeUp() {
        $this->createTable('setting', [
            'id' => $this->primaryKey()->unsigned(),
            'key' => $this->string()->notNull(),
            'value' => $this->text()->notNull(),
            'updated_at' => $this->integer()->unsigned()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown() {
        $this->dropTable('setting');
    }

}
