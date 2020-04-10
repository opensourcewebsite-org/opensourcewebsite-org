<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%user}}`.
 */
class m200402_225612_add_timezone_id_column_to_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'timezone_id', $this->integer()->unsigned());

        $this->addForeignKey(
            'fk-user_timezone_id-timezone_id',
            '{{%user}}',
            'timezone_id',
            '{{%timezone}}',
            'id'
        );

        $this->db->createCommand('UPDATE {{%user}}
        INNER JOIN {{%timezone}} ON {{%timezone}}.location = REPLACE({{%user}}.timezone, \'_\', \' \')
        SET {{%user}}.timezone_id = {{%timezone}}.id')->execute();

        $this->dropColumn('{{%user}}', 'timezone');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%user}}', 'timezone', $this->string()->notNull()->defaultValue('UTC'));

        $this->db->createCommand('UPDATE {{%user}}
        INNER JOIN {{%timezone}} ON {{%timezone}}.id = {{%user}}.timezone_id
        SET {{%user}}.timezone = REPLACE({{%timezone}}.location, \' \', \'_\')')->execute();

        $this->dropForeignKey(
            'fk-user_timezone_id-timezone_id',
            '{{%user}}'
        );

        $this->dropColumn('{{%user}}', 'timezone_id');
    }
}
