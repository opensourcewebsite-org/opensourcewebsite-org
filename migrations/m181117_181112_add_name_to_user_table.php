<?php

use yii\db\Migration;

/**
 * Class m181117_181112_add_name_to_user_table
 */
class m181117_181112_add_name_to_user_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'name', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%user}}', 'name');
    }
}