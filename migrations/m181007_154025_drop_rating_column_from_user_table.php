<?php

use yii\db\Migration;

/**
 * Handles dropping rating from table `user`.
 */
class m181007_154025_drop_rating_column_from_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('user', 'rating');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('user', 'rating', $this->integer()->unsigned());
    }
}
