<?php

use yii\db\Migration;

/**
 * Class m200524_000147_add_photo_file_id_to_ads_post_table
 */
class m200524_000147_add_photo_file_id_to_ads_post_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%ads_post}}', 'photo_file_id', $this->string()->after('description'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%ads_post}}', 'photo_file_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200524_000147_add_photo_file_id_to_ads_post_table cannot be reverted.\n";

        return false;
    }
    */
}
