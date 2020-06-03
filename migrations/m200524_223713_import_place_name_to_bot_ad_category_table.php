<?php

use yii\db\Migration;
use app\modules\bot\models\AdCategory;

/**
 * Class m200524_223713_import_place_name_to_bot_ad_category_table
 */
class m200524_223713_import_place_name_to_bot_ad_category_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        AdCategory::deleteAll();

        $categories = [
            [
                'find_name' => 'Купить',
                'place_name' => 'Продать',
            ],
            [
                'find_name' => 'Аренда',
                'place_name' => 'Аренда',
            ],
            [
                'find_name' => 'Услуги',
                'place_name' => 'Услуги',
            ],
        ];

        foreach ($categories as $category) {
            $this->insert('{{%bot_ad_category}}', [
                'find_name' => $category['find_name'],
                'place_name' => $category['place_name'],
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200524_223713_import_place_name_to_bot_ad_category_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200524_223713_import_place_name_to_bot_ad_category_table cannot be reverted.\n";

        return false;
    }
    */
}
