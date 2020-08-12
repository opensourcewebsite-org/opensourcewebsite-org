<?php

use yii\db\Migration;

/**
 * Class m180826_031345_user_unsigned_id
 */
class m180826_031345_user_unsigned_id extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('user', 'id', $this->integer()->unsigned());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('user', 'id', $this->integer());
    }
}
