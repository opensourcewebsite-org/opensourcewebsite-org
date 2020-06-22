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
            'company_id' => $this->integer()->unsigned()->notNull(),
            'status' => $this->tinyInteger()->unsigned()->notNull()->defaultValue(0),
            'remote_on' => $this->tinyInteger()->unsigned()->notNull()->defaultValue(0),
            'name' => $this->string()->notNull(),
            'requirements' => $this->text()->notNull(),
            'min_hourly_rate' => $this->decimal(10, 2)->unsigned(),
            'max_hourly_rate' => $this->decimal(10, 2)->unsigned(),
            'search_radius' => $this->integer(),
            'currency_id' => $this->integer()->unsigned()->notNull(),
            'conditions' => $this->text()->notNull(),
            'responsibilities' => $this->text()->notNull(),
            'gender_id' => $this->integer()->unsigned()->null()->defaultValue(null),
            'location_lat' => $this->string(255),
            'location_lon' => $this->string(255),
            'created_at' => $this->integer()->unsigned(),
            'renewed_at' => $this->integer()->unsigned(),
            'processed_at' => $this->integer()->unsigned(),
        ]);

        $this->addForeignKey(
            'fk-resume_company_id-company_id',
            '{{%resume}}',
            'company_id',
            '{{%company}}',
            'id'
        );

        $this->addForeignKey(
            'fk-resume_currency_id-currency_id',
            '{{%resume}}',
            'currency_id',
            '{{%currency}}',
            'id'
        );

        $this->addForeignKey(
            'fk-resume_gender_id-gender_id',
            '{{%resume}}',
            'gender_id',
            '{{%gender}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-resume_currency_id-currency_id',
            '{{%resume}}'
        );

        $this->dropForeignKey(
            'fk-resume_company_id-company_id',
            '{{%resume}}'
        );

        $this->dropForeignKey(
            'fk-resume_gender_id-gender_id',
            '{{%resume}}'
        );

        $this->dropTable('{{%resume}}');
    }
}
