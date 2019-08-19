<?php

use yii\db\Migration;

/**
 * Class m180826_031932_add_moqup_fk
 */
class m180826_031932_add_moqup_fk extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
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
        $this->dropForeignKey('moqup_user_id_fk', 'moqup');
    }
}
