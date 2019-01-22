<?php

use yii\db\Migration;

/**
 * Class m190122_112049_add_description_to_support_group_bot_client
 */
class m190122_112049_add_description_to_support_group_bot_client extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('support_group_bot_client', 'description', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('support_group_bot_client', 'description');
    }
}
