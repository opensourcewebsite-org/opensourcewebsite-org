<?php

use yii\db\Migration;

/**
 * Class m200602_164712_rename_bot_ad_category_table
 */
class m200602_164712_rename_bot_ad_category_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameTable('{{%bot_ad_category}}', '{{%ad_category}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->renameTable('{{%ad_category}}', '{{%bot_ad_category}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200602_164712_rename_bot_ad_category_table cannot be reverted.\n";

        return false;
    }
    */
}
