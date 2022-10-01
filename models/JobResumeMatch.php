<?php

namespace app\models;

use app\models\queries\JobResumeMatchQuery;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "job_resume_match".
 *
 * @property int $id
 * @property int $resume_id
 * @property int $vacancy_id
 *
 * @property Resume $resume
 * @property Vacancy $vacancy
 */
class JobResumeMatch extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%job_resume_match}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['resume_id', 'vacancy_id'], 'required'],
            [['resume_id', 'vacancy_id'], 'integer'],
            [['resume_id'], 'exist', 'skipOnError' => true, 'targetClass' => Resume::className(), 'targetAttribute' => ['resume_id' => 'id']],
            [['vacancy_id'], 'exist', 'skipOnError' => true, 'targetClass' => Vacancy::className(), 'targetAttribute' => ['vacancy_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'resume_id' => 'Resume ID',
            'vacancy_id' => 'Vacancy ID',
        ];
    }

    public static function find(): JobResumeMatchQuery
    {
        return new JobResumeMatchQuery(get_called_class());
    }

    public function getResume(): ActiveQuery
    {
        return $this->hasOne(Resume::className(), ['id' => 'resume_id']);
    }

    public function getVacancy(): ActiveQuery
    {
        return $this->hasOne(Vacancy::className(), ['id' => 'vacancy_id']);
    }

    public function isNew()
    {
        return !JobVacancyResponse::find()
            ->andWhere([
                'user_id' => $this->resume->user_id,
                'vacancy_id' => $this->vacancy_id,
            ])
            ->andWhere([
                'is not', 'viewed_at', null,
            ])
            ->exists();
    }
}
