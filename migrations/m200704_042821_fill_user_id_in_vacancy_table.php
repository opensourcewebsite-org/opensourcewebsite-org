<?php

use app\models\CompanyUser;
use app\models\Vacancy;
use yii\db\Migration;

/**
 * Class m200704_042821_fill_user_id_in_vacancy_table
 */
class m200704_042821_fill_user_id_in_vacancy_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $vacancies = Vacancy::find()->where(['IS', 'user_id', null])->all();
        foreach ($vacancies as $vacancy) {
            if ($vacancy->company_id) {
                $company = $vacancy->company;
                $companyUser = CompanyUser::findOne(['company_id' => $company->id]);
                $vacancy->user_id = $companyUser->user_id;
                $vacancy->save();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200704_042821_fill_user_id_in_vacancy_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200704_042821_fill_user_id_in_vacancy_table cannot be reverted.\n";

        return false;
    }
    */
}
