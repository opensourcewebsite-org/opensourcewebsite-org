<?php

use yii\db\Migration;

/**
 * Class m180930_061446_update_moqup_bytes_limit
 */
class m180930_061446_update_moqup_bytes_limit extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update('setting', ['value' => 1048576], ['key' => 'moqup_bytes_limit']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->update('setting', ['value' => 100000], ['key' => 'moqup_bytes_limit']);
    }
}
