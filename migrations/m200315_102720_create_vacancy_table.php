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
            'views' => $this->tinyInteger()->notNull()->defaultValue(0),
            'status' => $this->tineInteger()->notNull()->defaultValue(0),
            'name' => $this->string()->notNull(),
            'employment' => $this->string()->notNull(),
            'hours_of_employment' => $this->string()->notNull(),
            'requirements' => $this->text()->notNull(),
            'salary' => $this->string()->notNull(),
            'skills_description' => $this->text()->notNull(),
            'conditions' => $this->text()->notNull(),
            'responsibility' => $this->text()->notNull(),
            'sex' => $this->integer()->notNull(),
            'location_lat' => $this->integer(),
            'location_lon' => $this->integer(),
            'location_at' => $this->integer(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'renewed_at' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-vacancy_company_id-company_id',
            'vacancy',
            'company_id',
            'company',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-vacancy_company_id-company_id',
            'vacancy'
        );

        $this->dropTable('{{%vacancy}}');
    }
}
