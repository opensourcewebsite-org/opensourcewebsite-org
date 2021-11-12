<?php

use yii\db\Migration;

/**
 * Class m211112_054147_drop_wikipedia_tables
 */
class m211112_054147_drop_wikipedia_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('{{%fk-user_wiki_token-user_id}}', '{{%user_wiki_token}}');
        $this->dropForeignKey('{{%fk-user_wiki_token-language_id}}', '{{%user_wiki_token}}');

        $this->dropIndex('{{%idx-user_wiki_token-user_id}}', '{{%user_wiki_token}}');
        $this->dropIndex('{{%idx-user_wiki_token-language_id}}', '{{%user_wiki_token}}');

        $this->dropForeignKey('{{%fk-user_wiki_page-wiki_page_id}}', '{{%user_wiki_page}}');
        $this->dropForeignKey('{{%fk-user_wiki_page-user_id}}', '{{%user_wiki_page}}');

        $this->dropIndex('{{%idx-user_wiki_page-user_id}}', '{{%user_wiki_page}}');
        $this->dropIndex('{{%idx-user_wiki_page-wiki_page_id}}', '{{%user_wiki_page}}');

        $this->dropForeignKey('{{%fk-wiki_page-language}}', '{{%wiki_page}}');

        $this->dropIndex('{{%idx-wiki_page-title}}', '{{%wiki_page}}');
        $this->dropIndex('{{%idx-wiki_page-language_id}}', '{{%wiki_page}}');

        $this->dropTable('{{%wiki_language}}');
        $this->dropTable('{{%wiki_page}}');
        $this->dropTable('{{%user_wiki_page}}');
        $this->dropTable('{{%user_wiki_token}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m211112_054147_drop_wikipedia_tables cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m211112_054147_delete_wikipedia_tables cannot be reverted.\n";

        return false;
    }
    */
}
