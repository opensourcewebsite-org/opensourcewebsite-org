<?php

use app\models\Setting;
use yii\db\Migration;

/**
 * Class m190207_165555_add_new_settings_in_setting_table
 */
class m190207_165555_add_new_settings_in_setting_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $timestamp = strtotime(Yii::$app->formatter->asDatetime('now'));
        $model = new Setting();
        $model->key = 'days_count_to_calculate_active_rating';
        $model->value = '30';
        $model->updated_at = $timestamp;
        $model->save();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $model = Setting::find()->where(['key' => 'days_count_to_calculate_active_rating'])->one();
        $model->delete();
    }
}
