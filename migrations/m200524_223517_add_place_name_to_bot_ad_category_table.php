<?php

use yii\db\Migration;

/**
 * Class m200524_223517_add_place_name_to_bot_ad_category_table
 */
class m200524_223517_add_place_name_to_bot_ad_category_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%bot_ad_category}}', 'place_name', $this->string()->notNull()->after('find_name'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%bot_ad_category}}', 'place_name');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200524_223517_add_place_name_to_bot_ad_category_table cannot be reverted.\n";

        return false;
    }
    */
}
