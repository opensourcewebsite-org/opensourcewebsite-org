<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%sexuality}}`.
 */
class m200402_134043_create_sexuality_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%sexuality}}', [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->string()->notNull(),
        ]);

        $sexualities = [
            'Straight',
            'Gay',
            'Lesbian',
            'Bisexual',
        ];

        foreach ($sexualities as $sexuality) {
            $this->insert(
                '{{%sexuality}}',
                [ 'name' => $sexuality ]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%sexuality}}');
    }
}
