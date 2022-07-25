<?php

use yii\db\Migration;
use yii\db\Query;

/**
 * Class m211027_052307_upgrade_user_location_table
 */
class m211027_052307_upgrade_user_location_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $rows = (new Query())
            ->select([
                'user_id',
                'location_lat',
                'location_lon',
                'location_at',
            ])
            ->andWhere([
                'not',
                ['location_lat' => null],
            ])
            ->andWhere([
                'not',
                ['location_lon' => null],
            ])
            ->andWhere([
                'not',
                ['user_id' => null],
            ])
            ->from('{{%bot_user}}')
            ->all();

        foreach ($rows as $row) {
            $this->insert('{{%user_location}}', [
                'user_id' => $row['user_id'],
                'location_lat' => $row['location_lat'],
                'location_lon' => $row['location_lon'],
                'updated_at' => $row['location_at'] ?: time(),
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->truncateTable('{{%user_location}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m211027_052307_upgrade_user_location_table cannot be reverted.\n";

        return false;
    }
    */
}
