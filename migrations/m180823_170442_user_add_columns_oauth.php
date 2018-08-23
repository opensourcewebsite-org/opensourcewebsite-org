<?php

use yii\db\Migration;

/**
 * Class m180823_170442_user_add_columns_oauth
 */
class m180823_170442_user_add_columns_oauth extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('user', 'oauth_client', $this->string()->null()->after('is_email_confirmed'));
        $this->addColumn('user', 'oauth_client_user_id', $this->string()->null()->after('oauth_client'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180823_153156_user_add_columns_oauth cannot be reverted.\n";
        return false;
    }
}
