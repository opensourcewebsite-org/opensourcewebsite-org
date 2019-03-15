<?php

use yii\db\Migration;

/**
 * Class m190314_191916_add_url_in_wikinews_pages
 */
class m190314_191916_add_url_in_wikinews_pages extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('wikinews_page', 'wikinews_page_url', $this->text()->after('title'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('wikinews_page', 'wikinews_page_url');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190314_191916_add_url_in_wikinews_pages cannot be reverted.\n";

        return false;
    }
    */
}
