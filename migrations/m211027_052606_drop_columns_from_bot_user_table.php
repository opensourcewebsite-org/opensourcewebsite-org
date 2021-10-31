<?php

use yii\db\Migration;

/**
 * Handles dropping columns from table `{{%bot_user}}`.
 */
class m211027_052606_drop_columns_from_bot_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('{{%bot_user}}', 'location_lat');
        $this->dropColumn('{{%bot_user}}', 'location_lon');
        $this->dropColumn('{{%bot_user}}', 'location_at');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%bot_user}}', 'location_lat', $this->string());
        $this->addColumn('{{%bot_user}}', 'location_lon', $this->string());
        $this->addColumn('{{%bot_user}}', 'location_at', $this->integer()->unsigned());
    }
}
