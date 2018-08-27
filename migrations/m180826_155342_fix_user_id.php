<?php

use yii\db\Migration;

/**
 * Class m180826_155342_fix_user_id
 */
class m180826_155342_fix_user_id extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('moqup_user_id_fk', 'moqup');
        $this->alterColumn('user', 'id', $this->integer()->unsigned().' AUTO_INCREMENT');
        $this->addForeignKey(
            'moqup_user_id_fk',
            'moqup', 
            'user_id', 
            'user',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

    }
}
