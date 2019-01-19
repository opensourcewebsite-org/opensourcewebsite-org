<?php

use yii\db\Migration;

/**
 * Class m190118_174209_add_lat_long_in_support_group_bot_client_table
 */
class m190118_174209_add_lat_long_in_support_group_bot_client_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('support_group_bot_client', 'location_lat', $this->string());
        $this->addColumn('support_group_bot_client', 'location_lon', $this->string());
        $this->addColumn('support_group_bot_client', 'location_at', $this->integer()->unsigned());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('support_group_bot_client', 'location_lat');
        $this->dropColumn('support_group_bot_client', 'location_lon');
        $this->dropColumn('support_group_bot_client', 'location_at');
    }
}
