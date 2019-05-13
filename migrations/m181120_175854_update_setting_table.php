<?php

use yii\db\Migration;
use app\models\Setting;

/**
 * Class m181120_175854_update_setting_table
 */
class m181120_175854_update_setting_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("UPDATE `setting` SET `key` = 'moqup_quantity_value_per_one_rating' WHERE `key` = 'moqup_entries_limit'");
        $this->execute("DELETE FROM `setting` WHERE `key` = 'moqup_bytes_limit'");
        $settings = [
            'issue_quantity_value_per_one_rating' => '3',
            'moqup_html_field_max_value' => '100000',
            'moqup_css_field_max_value' => '100000',
            'issue_text_field_max_value' => '100000',
            'website_setting_min_vote_percent_to_apply_change' => '70',
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
        echo "m181120_175854_update_setting_table cannot be reverted.\n";

        return false;
    }
}