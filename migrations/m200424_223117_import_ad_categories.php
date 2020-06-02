<?php

use yii\db\Migration;

/**
 * Class m200424_223117_import_ad_categories
 */
class m200424_223117_import_ad_categories extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $categories = [
            'Продажа',
            'Аренда',
            'Услуги',
        ];

        foreach ($categories as $name) {
            $this->insert('{{%bot_ad_category}}', [
                'name' => $name,
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200424_223117_import_ad_categories cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200424_223117_import_ad_categories cannot be reverted.\n";

        return false;
    }
    */
}
