<?php

use yii\db\Migration;

/**
 * Handles the creation of table `wiki_language`.
 */
class m180910_150230_create_wiki_language_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->createTable('{{%wiki_language}}', [
            'id' => $this->primaryKey()->unsigned(),
            'code' => $this->string()->notNull(),
            'name' => $this->string()->notNull(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%wiki_language}}');
    }
}
