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
            'type' => $this->smallInteger()->notNull(),
        ]);

        $genders = [
            0, // FEMALE
            1, //MALE
        ];

        foreach ($genders as $gender) {
            $this->insert(
                '{{%gender}}',
                [ 'type' => $gender ]
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
