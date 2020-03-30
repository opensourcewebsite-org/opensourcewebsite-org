<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%debt_redistribution}}`.
 */
class m200326_202143_create_debt_redistribution_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%debt_redistribution}}', [
            'id'           => $this->primaryKey() ->unsigned(),
            'from_user_id' => $this->integer()    ->unsigned()->notNull(),
            'to_user_id'   => $this->integer()    ->unsigned()->notNull(),
            'max_amount'   => $this->integer()    ->unsigned()->defaultValue(0)
                ->comment('"NULL" - no limit - allow any amount. "0" - limit is 0, so deny to redistribute.'),
            'priority'     => $this->tinyInteger()->unsigned()->notNull()->defaultValue(0)
                ->comment('"1" - the highest. "0" - no priority.'),
        ]);

        $this->createIndex(
            'uidx-debt_redistribution-from_user_id-to_user_id',
            '{{%debt_redistribution}}',
            ['from_user_id', 'to_user_id'],
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%debt_redistribution}}');
    }
}
