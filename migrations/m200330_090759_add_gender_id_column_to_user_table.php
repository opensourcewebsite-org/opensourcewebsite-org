<?php

use yii\db\Migration;
use yii\db\Query;

/**
 * Handles adding columns to table `{{%user}}`.
 */
class m200330_090759_add_gender_id_column_to_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'gender_id', $this->integer()->unsigned());

        $this->addForeignKey(
            'fk-user_gender_id-gender_id',
            '{{%user}}',
            'gender_id',
            '{{%gender}}',
            'id'
        );

        $genders = (new Query())->select([ 'type' ])->from('{{%gender}}')->all();
        foreach ($genders as $gender) {
            $genderId = (new Query())->select([ 'id' ])->from('{{%gender}}')->where([ 'type' => $gender ])->one();
            $this->update(
                '{{%user}}',
                [ 'gender_id' => $genderId ],
                [ 'gender' => $gender ]
            );
        }

        $this->dropColumn('{{%user}}', 'gender');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%user}}', 'gender', $this->tinyInteger()->unsigned());

        $genders = (new Query())->select([ 'id' ])->from('{{%gender}}')->all();
        foreach ($genders as $gender) {
            $genderId = (new Query())->select([ 'type' ])->from('{{%gender}}')->where([ 'id' => $gender ])->one();
            $this->update(
                '{{%user}}',
                [ 'gender' => $gender ],
                [ 'gender_id' => $genderId ]
            );
        }

        $this->dropForeignKey(
            'fk-user_gender_id-gender_id',
            '{{%user}}'
        );

        $this->dropColumn('{{%user}}', 'gender_id');
    }
}
