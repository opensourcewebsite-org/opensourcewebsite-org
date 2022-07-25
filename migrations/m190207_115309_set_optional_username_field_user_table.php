<?php

use yii\db\Migration;

/**
 * Class m190207_115309_set_optional_username_field_user_table
 */
class m190207_115309_set_optional_username_field_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('user', 'username', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('user', 'username', $this->string()->notNull());
    }
}
