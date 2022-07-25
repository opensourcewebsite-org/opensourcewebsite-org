<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Class JobResumeKeyword
 *
 * @package app\modules\bot\models
 */
class JobResumeKeyword extends ActiveRecord
{
    /** @inheritDoc */
    public static function tableName()
    {
        return '{{%job_resume_keyword}}';
    }

    /** @inheritDoc */
    public function rules()
    {
        return [
            [['resume_id', 'job_keyword_id'], 'required'],
            [['resume_id', 'job_keyword_id'], 'integer'],
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
