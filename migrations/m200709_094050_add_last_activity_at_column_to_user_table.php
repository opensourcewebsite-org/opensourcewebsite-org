<?php

use yii\db\Migration;
use app\models\User;

/**
 * Handles adding columns to table `{{%user}}`.
 */
class m200709_094050_add_last_activity_at_column_to_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'last_activity_at', $this->integer()->unsigned()->notNull());
        $this->alterColumn('{{%user}}', 'created_at', $this->integer()->unsigned()->notNull());
        $this->alterColumn('{{%user}}', 'updated_at', $this->integer()->unsigned()->notNull());

        $models = User::find()->all();
        foreach ($models as $model) {
            $this->update(
                'user',
                ['last_activity_at' => $model->created_at],
                ['id' => $model->id]
            );
        }

        $this->dropColumn('currency_exchange_order', 'renewed_at');
        $this->dropColumn('{{%vacancy}}', 'renewed_at');
        $this->dropColumn('{{%ad_offer}}', 'renewed_at');
        $this->dropColumn('{{%ad_search}}', 'renewed_at');
        $this->dropColumn('{{%resume}}', 'renewed_at');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('currency_exchange_order', 'renewed_at', $this->integer()->unsigned()->notNull());
        $this->addColumn('%{{%vacancy}}', 'renewed_at', $this->integer()->unsigned()->notNull());
        $this->addColumn('{{%ad_offer}}', 'renewed_at', $this->integer()->unsigned()->notNull());
        $this->addColumn('{{%ad_search}}', 'renewed_at', $this->integer()->unsigned()->notNull());
        $this->addColumn('{{%resume}}', 'renewed_at', $this->integer()->unsigned()->notNull());

        $this->alterColumn('{{%user}}', 'created_at', $this->integer()->notNull());
        $this->alterColumn('{{%user}}', 'updated_at', $this->integer()->notNull());
        $this->dropColumn('{{%user}}', 'last_activity_at');
    }
}
