<?php

use yii\db\Migration;

/**
 * Handles the creation of table `setting_value`.
 */
class m181128_162050_create_setting_value_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('setting_value', [
            'id' => $this->primaryKey()->unsigned(),
            'setting_id' => $this->integer()->unsigned()->notNull(),
            'value' => $this->text()->notNull(),
            'is_current' => $this->tinyInteger(1)->unsigned()->defaultValue(0)->notNull(),
            'updated_at' => $this->integer()->unsigned(),
        ]);

        // creates index for column `setting_id`
        $this->createIndex(
            'idx-setting_value-setting_id',
            'setting_value',
            'setting_id'
        );

        // add foreign key for table `setting`
        $this->addForeignKey(
            'fk-setting_value-setting_id',
            'setting_value',
            'setting_id',
            'setting',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

        // drops foreign key for table `issue`
        $this->dropForeignKey(
            'fk-setting_value-setting_id',
            'setting_value'
        );

        // drops index for column `issue_id`
        $this->dropIndex(
            'idx-setting_value-setting_id',
            'setting_value'
        );

        $this->dropTable('setting_value');
    }
}
