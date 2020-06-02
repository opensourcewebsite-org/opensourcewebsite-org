<?php

use yii\db\Migration;

/**
 * Class m200602_164913_rename_bot_ad_keyword_table
 */
class m200602_164913_rename_bot_ad_keyword_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameTable('{{%bot_ad_keyword}}', '{{%ad_keyword}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->renameTable('{{%ad_keyword}}', '{{%bot_ad_keyword}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200602_164913_rename_bot_ad_keyword_table cannot be reverted.\n";

        return false;
    }
    */
}
