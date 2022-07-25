<?php

use yii\db\Migration;

/**
 * Class m211112_054141_drop_wikinews_tables
 */
class m211112_054141_drop_wikinews_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('{{%fk-wikinews_page-language}}', '{{%wikinews_page}}');
        $this->dropForeignKey('{{%fk-wikinews_page-created_by}}', '{{%wikinews_page}}');

        $this->dropIndex('{{%idx-wikinews_page-title}}', '{{%wikinews_page}}');
        $this->dropIndex('{{%idx-wikinews_page-language_id}}', '{{%wikinews_page}}');
        $this->dropIndex('{{%idx-wikinews_page-created_by}}', '{{%wikinews_page}}');

        $this->dropTable('{{%wikinews_language}}');
        $this->dropTable('{{%wikinews_page}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m211112_054141_drop_wikinews_tables cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m211112_054141_delete_wikinews_tables cannot be reverted.\n";

        return false;
    }
    */
}
