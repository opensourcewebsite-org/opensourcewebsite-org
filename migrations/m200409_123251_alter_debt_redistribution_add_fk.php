<?php

use yii\db\Migration;

/**
 * Class m200409_123251_alter_debt_redistribution_add_fk
 */
class m200409_123251_alter_debt_redistribution_add_fk extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addForeignKey(
            '{{%fk-debt_redistribution-from_user_id-user-id}}',
            '{{%debt_redistribution}}',
            'from_user_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );
        $this->addForeignKey(
            '{{%fk-debt_redistribution-to_user_id-user-id}}',
            '{{%debt_redistribution}}',
            'to_user_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );
        $this->addForeignKey(
            '{{%fk-debt_redistribution-currency_id-currency-id}}',
            '{{%debt_redistribution}}',
            'currency_id',
            '{{%currency}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('{{%fk-debt_redistribution-from_user_id-user-id}}', '{{%debt_redistribution}}');
        $this->dropForeignKey('{{%fk-debt_redistribution-to_user_id-user-id}}', '{{%debt_redistribution}}');
        $this->dropForeignKey('{{%fk-debt_redistribution-currency_id-currency-id}}', '{{%debt_redistribution}}');
    }
}
