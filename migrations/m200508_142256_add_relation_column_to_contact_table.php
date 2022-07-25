<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%contact}}`.
 */
class m200508_142256_add_relation_column_to_contact_table extends Migration
{
    /**
     * {@inheritdoc}
     */
     public function safeUp()
     {
         $this->addColumn('{{%contact}}', 'relation', $this->tinyInteger()->unsigned()->defaultValue(0)->notNull());
     }

     /**
      * {@inheritdoc}
      */
     public function safeDown()
     {
         $this->dropColumn('{{%contact}}', 'relation');
     }
}
