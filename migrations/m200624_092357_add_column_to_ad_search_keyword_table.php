<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%ad_search_keyword}}`.
 */
class m200624_092357_add_column_to_ad_search_keyword_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%ad_search_keyword}}', 'id', $this->primaryKey()->unsigned()->first());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%ad_search_keyword}}', 'id');
    }
}
