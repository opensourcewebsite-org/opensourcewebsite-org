<?php

use yii\db\Migration;

/**
 * Class m200529_222226_add_currency_id_to_ads_post
 */
class m200529_222226_add_currency_id_to_ads_post extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%ads_post}}', 'currency_id', $this->integer()->unsigned()->notNull()->after('photo_file_id'));

        $this->addForeignKey(
            'fk-ads_post_currency_id-currency_id',
            '{{%ads_post}}',
            'currency_id',
            '{{%currency}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-ads_post_currency_id-currency_id');

        $this->dropColumn('{{%ads_post}}', 'currency_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200529_222226_add_currency_id_to_ads_post cannot be reverted.\n";

        return false;
    }
    */
}
