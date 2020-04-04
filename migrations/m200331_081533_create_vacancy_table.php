<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%vacancy}}`.
 */
class m200331_081533_create_vacancy_table extends Migration
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
            'requirements' => $this->text()->notNull(),
            'min_hourly_rate' => $this->decimal(10, 2)->unsigned(),
            'max_hourly_rate' => $this->decimal(10, 2)->unsigned(),
            'currency_id' => $this->integer()->unsigned()->notNull(),
            'conditions' => $this->text()->notNull(),
            'responsibilities' => $this->text()->notNull(),
            'gender_id' => $this->integer()->unsigned()->null()->defaultValue(null),
            'location_lat' => $this->string(255),
            'location_lon' => $this->string(255),
            'location_at' => $this->integer()->unsigned(),
            'renewed_at' => $this->integer()->unsigned(),
        ]);

        $this->addForeignKey(
            'fk-vacancy_company_id-company_id',
            '{{%vacancy}}',
            'company_id',
            '{{%company}}',
            'id'
        );

        $this->addForeignKey(
            'fk-vacancy_currency_id-currency_id',
            '{{%vacancy}}',
            'currency_id',
            '{{%currency}}',
            'id'
        );

        $this->addForeignKey(
            'fk-vacancy_gender_id-gender_id',
            '{{%vacancy}}',
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
            'fk-vacancy_currency_id-currency_id',
            '{{%vacancy}}'
        );

        $this->dropForeignKey(
            'fk-vacancy_company_id-company_id',
            '{{%vacancy}}'
        );

        $this->dropForeignKey(
            'fk-vacancy_gender_id-gender_id',
            '{{%vacancy}}'
        );

        $this->dropTable('{{%vacancy}}');
    }
}
