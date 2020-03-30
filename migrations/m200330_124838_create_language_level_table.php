<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%language_level}}`.
 */
class m200330_124838_create_language_level_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%language_level}}', [
            'id' => $this->primaryKey()->unsigned(),
            'code' => $this->string(),
            'description' => $this->string()->notNull(),
            'value' => $this->integer()->unsigned()->notNull(),
        ]);

        $levels = [
            [ 'A1', 'Beginner', 1 ],
            [ 'A2', 'Elementary', 2 ],
            [ 'B1', 'Intermediate', 3 ],
            [ 'B2', 'Upper-Intermediate', 4 ],
            [ 'C1', 'Advanced', 5 ],
            [ 'C2', 'Proficient', 6 ],
            [ null , 'Native', 7 ],
        ];

        foreach ($levels as $level) {
            $this->insert(
                '{{%language_level}}',
                [
                    'code' => $level[0],
                    'description' => $level[1],
                    'value' => $level[2],
                ]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%language_level}}');
    }
}
