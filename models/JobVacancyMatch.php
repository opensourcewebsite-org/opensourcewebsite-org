<?php

namespace app\models;

use app\models\queries\JobVacancyMatchQuery;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "job_vacancy_match".
 *
 * @property int $id
 * @property int $vacancy_id
 * @property int $resume_id
 *
 * @property Resume $resume
 * @property Vacancy $vacancy
 */
class JobVacancyMatch extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%job_vacancy_match}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['vacancy_id', 'resume_id'], 'required'],
            [['vacancy_id', 'resume_id'], 'integer'],
            [['resume_id'], 'exist', 'skipOnError' => true, 'targetClass' => Resume::className(), 'targetAttribute' => ['resume_id' => 'id']],
            [['vacancy_id'], 'exist', 'skipOnError' => true, 'targetClass' => Vacancy::className(), 'targetAttribute' => ['vacancy_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'vacancy_id' => 'Vacancy ID',
            'resume_id' => 'Resume ID',
        ];
    }

    public static function find(): JobVacancyMatchQuery
    {
        return new JobVacancyMatchQuery(get_called_class());
    }

    public function getVacancy(): ActiveQuery
    {
        return $this->hasOne(Vacancy::className(), ['id' => 'vacancy_id']);
    }

    public function getResume(): ActiveQuery
    {
        return $this->hasOne(Resume::className(), ['id' => 'resume_id']);
    }

    public function isNew()
    {
        return !JobResumeResponse::find()
            ->andWhere([
                'user_id' => $this->vacancy->user_id,
                'resume_id' => $this->resume_id,
            ])
            ->andWhere([
                'is not', 'viewed_at', null,
            ])
            ->exists();
    }
}
