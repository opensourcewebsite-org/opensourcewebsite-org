<?php

use yii\db\Migration;
use yii\db\Query;

/**
 * Class m210929_081450_upgrade_user_email_table
 */
class m210929_081450_upgrade_user_email_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $rows = (new Query())
            ->select([
                'id',
                'email',
                'is_authenticated',
            ])
            ->where([
                'not',
                ['email' => null],
            ])
            ->from('{{%user}}')
            ->all();

        foreach ($rows as $row) {
            $this->insert('{{%user_email}}', [
                'user_id' => $row['id'],
                'email' => $row['email'],
                'confirmed_at' => $row['is_authenticated'] ? time() : null,
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->truncateTable('{{%user_email}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210929_081450_upgrade_user_email_table cannot be reverted.\n";

        return false;
    }
    */
}
