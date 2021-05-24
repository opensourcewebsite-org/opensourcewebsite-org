<?php

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Class JobKeyword
 *
 * @package app\modules\bot\models
 *
 * @property int $id
 * @property string $keyword
 */
class JobKeyword extends ActiveRecord
{

    public static function tableName(): string
    {
        return '{{%job_keyword}}';
    }

    public function rules(): array
    {
        return [
            [['keyword'], 'required'],
            [['keyword'], 'string'],
            [['keyword'], 'unique'],
        ];
    }

    public function getVacancies(): ActiveQuery
    {
        return $this->hasMany(Vacancy::class, ['id' => 'vacancy_id'])
            ->viaTable('{{%job_vacancy_keyword}}', ['job_keyword_id' => 'id']);
    }

    public function getLabel(): string
    {
        return $this->keyword;
    }

    public function getResumes(): ActiveQuery
    {
        return $this->hasMany(Resume::class, ['id' => 'resume_id'])
            ->viaTable('{{%job_resume_keyword}}', ['job_keyword_id' => 'id']);
    }
}
