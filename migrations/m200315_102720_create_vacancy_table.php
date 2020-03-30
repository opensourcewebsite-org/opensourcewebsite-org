<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%vacancy}}`.
 */
class m200315_102720_create_vacancy_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%vacancy}}', [
            'id' => $this->primaryKey()->unsigned(),
            'company_id' => $this->integer()->unsigned()->notNull(),
            'status' => $this->tinyInteger()->unsigned()->notNull()->defaultValue(0),
            'name' => $this->string()->notNull(),
            'employment' => $this->string()->notNull(),
            'hours_of_employment' => $this->string()->notNull(),
            'requirements' => $this->text()->notNull(),
            'salary' => $this->string()->notNull(),
            'currency_id' => $this->integer()->unsigned()->notNull(),
            'skills_description' => $this->text()->notNull(),
            'conditions' => $this->text()->notNull(),
            'responsibilities' => $this->text()->notNull(),
            'gender' => $this->tinyInteger()->null()->defaultValue(null),
            'location_lat' => $this->string(255),
            'location_lon' => $this->string(255),
            'location_at' => $this->integer()->unsigned(),
            'created_at' => $this->integer()->unsigned()->notNull(),
            'renewed_at' => $this->integer()->unsigned()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-vacancy_company_id-company_id',
            'vacancy',
            'company_id',
            'company',
            'id'
        );

        $this->addForeignKey(
            'fk-vacancy_currency_id-currency_id',
            'vacancy',
            'currency_id',
            'currency',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-vacancy_currency_id-currency_id',
            'vacancy'
        );

        $this->dropForeignKey(
            'fk-vacancy_company_id-company_id',
            'vacancy'
        );

        $this->dropTable('{{%vacancy}}');
    }
}
