<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%resume}}`.
 */
class m200622_121041_create_resume_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%resume}}', [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer()->unsigned()->notNull(),
            'status' => $this->tinyInteger()->unsigned()->notNull()->defaultValue(0),
            'remote_on' => $this->tinyInteger()->unsigned()->notNull()->defaultValue(0),
            'name' => $this->string()->notNull(),
            'experiences' => $this->text(),
            'min_hourly_rate' => $this->decimal(10, 2)->unsigned(),
            'search_radius' => $this->integer()->unsigned(),
            'currency_id' => $this->integer()->unsigned()->notNull(),
            'expectations' => $this->text(),
            'skills' => $this->text(),
            'location_lat' => $this->string(),
            'location_lon' => $this->string(),
            'created_at' => $this->integer()->unsigned()->notNull(),
            'renewed_at' => $this->integer()->unsigned()->notNull(),
            'processed_at' => $this->integer()->unsigned(),
        ]);

        $this->addForeignKey(
            'fk-resume_currency_id-currency_id',
            '{{%resume}}',
            'currency_id',
            '{{%currency}}',
            'id'
        );

        $this->addForeignKey(
            'fk-resume-user_id',
            '{{%resume}}',
            'user_id',
            '{{%user}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-resume-user_id', '{{%resume}}');

        $this->dropForeignKey(
            'fk-resume_currency_id-currency_id',
            '{{%resume}}'
        );

        $this->dropTable('{{%resume}}');
    }
}
