<?php

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Class JobVacancyKeyword
 *
 * @package app\modules\bot\models
 */
class JobVacancyKeyword extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%job_vacancy_keyword}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['vacancy_id', 'job_keyword_id'], 'required'],
            [['vacancy_id', 'job_keyword_id'], 'integer'],
        ];
    }

    public function getVacancy(): ActiveQuery
    {
        return $this->hasOne(Vacancy::className(), ['id' => 'vacancy_id']);
    }

    public function getKeyword(): ActiveQuery
    {
        return $this->hasOne(JobKeyword::class, ['id' => 'job_keyword_id']);
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->keyword->keyword;
    }
}
