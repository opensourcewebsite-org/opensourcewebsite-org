<?php

use yii\db\Migration;
use yii\db\Query;

/**
 * Handles the creation of table `{{%gender}}`.
 */
class m200330_085104_create_gender_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%gender}}', [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->string()->notNull(),
        ]);

        $this->alterColumn('{{%gender}}', 'id', $this->tinyInteger()->unsigned() . ' NOT NULL AUTO_INCREMENT');

        $genders = [
            'Female',
            'Male',
        ];

        foreach ($genders as $gender) {
            $this->insert(
                '{{%gender}}',
                [ 'name' => $gender ]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%gender}}');
    }
}
