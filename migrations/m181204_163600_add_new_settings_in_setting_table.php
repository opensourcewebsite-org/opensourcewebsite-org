<?php

use app\models\Setting;
use yii\db\Migration;

/**
 * Class m181204_163600_add_new_settings_in_setting_table
 */
class m181204_163600_add_new_settings_in_setting_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $settings = [
            'support_group_quantity_value_per_one_rating' => '1',
            'support_group_bot_quantity_value_per_one_rating' => '1',
            'support_group_member_quantity_value_per_one_rating' => '1',
        ];
        foreach ($settings as $key => $value) {
            $timestamp = strtotime(Yii::$app->formatter->asDatetime('now'));
            $model = new Setting();
            $model->key = $key;
            $model->value = $value;
            $model->updated_at = $timestamp;
            $model->save();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $model = Setting::find()->where(['key' => ['support_group_quantity_value_per_one_rating', 'support_group_bot_quantity_value_per_one_rating', 'support_group_member_quantity_value_per_one_rating']])->one();
        $model->delete();
    }

    /*
// Use up()/down() to run migration code without a transaction.
public function up()
{

}

public function down()
{
echo "m181204_163600_add_new_settings_in_setting_table cannot be reverted.\n";

return false;
}
 */
}
