<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%bot_chat_member}}`.
 */
class m221022_063936_add_columns_to_bot_chat_member_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%bot_chat_member}}', 'membership_tariff_days', $this->smallInteger()->unsigned()->after('membership_note'));
        $this->addColumn('{{%bot_chat_member}}', 'membership_tariff_price', $this->decimal(15, 2)->unsigned()->after('membership_note'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%bot_chat_member}}', 'membership_tariff_days');
        $this->dropColumn('{{%bot_chat_member}}', 'membership_tariff_price');
    }
}
