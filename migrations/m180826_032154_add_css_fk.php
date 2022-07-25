<?php

use yii\db\Migration;

/**
 * Class m180826_032154_add_css_fk
 */
class m180826_032154_add_css_fk extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addForeignKey(
            'css_moqup_id_fk',
            'css', 
            'moqup_id', 
            'moqup',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('css_moqup_id_fk', 'css');
    }
}
