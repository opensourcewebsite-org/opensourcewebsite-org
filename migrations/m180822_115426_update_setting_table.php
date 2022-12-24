<?php

use yii\db\Migration;

/**
 * Class m180822_115426_update_setting_table
 */
class m180822_115426_update_setting_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $now = strtotime("now");

        $this->execute("INSERT INTO `setting` (`id`, `key`, `value`, `updated_at`) VALUES
        (1, 'moqup_entries_limit', '20', " . $now . "),
        (2, 'moqup_bytes_limit', '100000', " . $now . ")
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180822_115426_update_setting_table cannot be reverted.\n";

        return false;
    }

    /*
      // Use up()/down() to run migration code without a transaction.
      public function up()
      {

      }

      public function down()
      {
      echo "m180822_115426_update_setting_table cannot be reverted.\n";

      return false;
      }
     */
}
