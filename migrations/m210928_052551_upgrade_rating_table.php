<?php

use yii\db\Migration;
use app\models\Rating;

/**
 * Class m210928_052551_upgrade_rating_table
 */
class m210928_052551_upgrade_rating_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        Rating::deleteAll(['type' => 0]);
        Rating::deleteAll(['type' => 3]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210928_052551_upgrade_rating_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210928_052551_upgrade_rating_table cannot be reverted.\n";

        return false;
    }
    */
}
