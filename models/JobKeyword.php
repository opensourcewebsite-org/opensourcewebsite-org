<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Class JobKeyword
 *
 * @package app\modules\bot\models
 */
class JobKeyword extends ActiveRecord
{
    /** @inheritDoc */
    public static function tableName()
    {
        return '{{%job_keyword}}';
    }

    /** @inheritDoc */
    public function rules()
    {
        return [
            [['keyword'], 'required'],
            [['keyword'], 'string'],
            [['keyword'], 'unique'],
        ];
    }

    public function getVacancies()
    {
        return $this->hasMany(Vacancy::className(), ['id' => 'vacancy_id'])
            ->viaTable('{{%job_vacancy_keyword}}', ['job_keyword_id' => 'id']);
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->keyword;
    }

    public function getResumes()
    {
        return $this->hasMany(Resume::className(), ['id' => 'resume_id'])
            ->viaTable('{{%job_resume_keyword}}', ['job_keyword_id' => 'id']);
    }

    /** @inheritDoc */
    public static function find()
    {
        $query = parent::find();
        $query->orderBy(['keyword' => SORT_ASC]);

        return $query;
    }
}
