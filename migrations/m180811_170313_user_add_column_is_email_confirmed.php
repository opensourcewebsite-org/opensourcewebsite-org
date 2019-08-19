<?php

use yii\db\Migration;

/**
 * Class m180811_170313_user_add_column_is_email_confirmed
 */
class m180811_170313_user_add_column_is_email_confirmed extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('user', 'is_email_confirmed', $this->tinyInteger()->null()->after('email'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180811_170313_user_add_column_is_email_confirmed cannot be reverted.\n";

        return false;
    }
}
