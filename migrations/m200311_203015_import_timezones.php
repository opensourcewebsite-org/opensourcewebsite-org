<?php

use yii\db\Migration;

/**
 * Class m200311_203015_import_timezones
 */
class m200311_203015_import_timezones extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('SET FOREIGN_KEY_CHECKS=0');
        $this->truncateTable('{{%timezone}}');

        $timestamp = time();
        foreach (timezone_identifiers_list() as $code => $name) {
            date_default_timezone_set($name);
            $offset = date('Z', $timestamp);

            $this->execute("INSERT INTO `timezone` (`code`, `name`, `offset`) VALUES ('$code', '$name', '$offset');");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200311_203015_import_timezones cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200311_203015_import_timezones cannot be reverted.\n";

        return false;
    }
    */
}
