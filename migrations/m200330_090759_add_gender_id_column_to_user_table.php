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
        $this->addColumn('{{%user}}', 'gender_id', $this->tinyInteger()->unsigned());

        $this->addForeignKey(
            'fk-user_gender_id-gender_id',
            '{{%user}}',
            'gender_id',
            '{{%gender}}',
            'id'
        );

        $genderId = (new Query())->select([ 'id' ])->from('{{%gender}}')->where([ 'name' => 'Female' ])->one();
        $this->update(
            '{{%user}}',
            [ 'gender_id' => $genderId ],
            [ 'gender' => 0 ]
        );

        $genderId = (new Query())->select([ 'id' ])->from('{{%gender}}')->where([ 'name' => 'male' ])->one();
        $this->update(
            '{{%user}}',
            [ 'gender_id' => $genderId ],
            [ 'gender' => 1 ]
        );

        $this->dropColumn('{{%user}}', 'gender');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%user}}', 'gender', $this->tinyInteger()->unsigned());

        $genderId = (new Query())->select([ 'type' ])->from('{{%gender}}')->where([ 'name' => 'female' ])->one();
        $this->update(
            '{{%user}}',
            [ 'gender' => 0 ],
            [ 'gender_id' => $genderId ]
        );

        $genderId = (new Query())->select([ 'type' ])->from('{{%gender}}')->where([ 'name' => 'male' ])->one();
        $this->update(
            '{{%user}}',
            [ 'gender' => 1 ],
            [ 'gender_id' => $genderId ]
        );

        $this->dropForeignKey(
            'fk-user_gender_id-gender_id',
            '{{%user}}'
        );

        $this->dropColumn('{{%user}}', 'gender_id');
    }
}
