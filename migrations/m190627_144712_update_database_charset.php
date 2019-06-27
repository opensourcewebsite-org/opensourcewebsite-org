<?php

use yii\db\Migration;

/**
 * Class m190627_144712_update_database_charset
 */
class m190627_144712_update_database_charset extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        preg_match('/' . 'dbname' . '=([^;]*)/', $this->db->dsn, $match);
        $db_name = $match[1];
        $this->execute("ALTER DATABASE $db_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190627_144712_update_database_charset cannot be reverted.\n";

        return false;
    }
}
