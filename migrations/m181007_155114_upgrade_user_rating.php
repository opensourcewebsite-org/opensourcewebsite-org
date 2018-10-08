<?php

use yii\db\Migration;
use app\models\Rating;
use app\models\User;

/**
 * Class m181007_155114_upgrade_user_rating
 */
class m181007_155114_upgrade_user_rating extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $users = User::find()->where(['is_email_confirmed' => true])->all();
        $rows = [];

        if (!empty($users)) {
            foreach ($users as $user) {
                $rows[] = [$user->id, 1, 1, Rating::CONFIRM_EMAIL, (new \yii\db\Expression('unix_timestamp()'))];
            }
        }

        if (!empty($rows)) {
            $this->batchInsert('rating', ['user_id', 'balance', 'amount', 'type', 'created_at'], $rows);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return true;
    }
}
