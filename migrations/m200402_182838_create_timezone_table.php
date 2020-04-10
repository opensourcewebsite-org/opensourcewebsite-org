<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%timezone}}`.
 */
class m200402_182838_create_timezone_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%timezone}}', [
            'id' => $this->primaryKey()->unsigned(),
            'location' => $this->string()->notNull(),
            'offset' => $this->integer()->notNull(),
        ]);

        /* Creating array of timezones */
        $timezones = [];
        foreach (timezone_identifiers_list() as $timezone) {
            $datetime = new DateTimeZone($timezone);
            $offset = $datetime->getOffset(new DateTime("now", new DateTimeZone("UTC")));
            $timezones[] = [
                'zone' => str_replace('_', ' ', $timezone),
                'offset' => $offset,
            ];
        }

        foreach ($timezones as $timezone) {
            $this->insert(
                '{{%timezone}}',
                [
                    'location' => $timezone['zone'],
                    'offset' => $timezone['offset'],
                ]
            );
        }

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%timezone}}');
    }
}
