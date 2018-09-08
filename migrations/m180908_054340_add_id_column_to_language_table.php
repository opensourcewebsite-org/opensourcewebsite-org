<?php

use yii\db\Migration;

/**
 * Handles adding id to table `language`.
 */
class m180908_054340_add_id_column_to_language_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropPrimaryKey('', '{{%language}}');
        $this->addColumn('{{%language}}', 'id', $this->primaryKey()->unsigned()->first());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%language}}', 'id');
    }
}
