<?php

use yii\db\Migration;

/**
 * Class m190128_153446_add_modup_comment_fk
 */
class m190128_153446_add_moqup_comment_fk extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addForeignKey(
            'fk-moqup_comment-moqup_id',
            'moqup_comment',
            'moqup_id',
            'moqup',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-moqup_comment-user_id',
            'moqup_comment',
            'user_id',
            'user',
            'id'
        );


        $this->createIndex(
            'idx-moqup_comment-parent_id',
            'moqup_comment',
            'parent_id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-moqup_comment-moqup_id', 'moqup_comment');
        $this->dropForeignKey('fk-moqup_comment-user_id', 'moqup_comment');
        $this->dropIndex('idx-moqup_comment-parent_id', 'moqup_comment');
    }
}
