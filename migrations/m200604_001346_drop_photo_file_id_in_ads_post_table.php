<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{{%photo_file_id_in_ads_post}}`.
 */
class m200604_001346_drop_photo_file_id_in_ads_post_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('{{%ads_post}}', 'photo_file_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%ads_post}}', 'photo_file_id', $this->string()->after('description'));
    }
}
