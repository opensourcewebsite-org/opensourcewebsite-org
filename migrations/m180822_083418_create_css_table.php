<?php

use yii\db\Migration;

/**
 * Handles the creation of table `css`.
 */
class m180822_083418_create_css_table extends Migration {

    /**
     * {@inheritdoc}
     */
    public function safeUp() {
        $this->createTable('css', [
            'id' => $this->primaryKey()->unsigned(),
            'moqup_id' => $this->integer()->notNull()->unsigned(),
            'css' => $this->text(),
            'created_at' => $this->integer()->unsigned(),
            'updated_at' => $this->integer()->unsigned()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown() {
        $this->dropTable('css');
    }

}
