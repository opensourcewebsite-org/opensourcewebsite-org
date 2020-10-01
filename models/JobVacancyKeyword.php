<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Class JobVacancyKeyword
 *
 * @package app\modules\bot\models
 */
class JobVacancyKeyword extends ActiveRecord
{
    /** @inheritDoc */
    public static function tableName()
    {
        return '{{%job_vacancy_keyword}}';
    }

    /** @inheritDoc */
    public function rules()
    {
        return [
            [['vacancy_id', 'job_keyword_id'], 'required'],
            [['vacancy_id', 'job_keyword_id'], 'integer'],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getKeyword()
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
