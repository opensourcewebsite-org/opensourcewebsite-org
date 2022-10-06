<?php

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Class JobResumeKeyword
 *
 * @package app\modules\bot\models
 */
class JobResumeKeyword extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%job_resume_keyword}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['resume_id', 'job_keyword_id'], 'required'],
            [['resume_id', 'job_keyword_id'], 'integer'],
        ];
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
