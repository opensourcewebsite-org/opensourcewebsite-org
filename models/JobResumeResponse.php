<?php
declare(strict_types=1);

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $user_id
 * @property int $resume_id
 * @property int $viewed_at
 * @property int $archived_at
 */
class JobResumeResponse extends ActiveRecord
{
    public static function tableName(): string
    {
        return "job_resume_response";
    }

    public function rules(): array
    {
        return [
            [
                ['user_id','resume_id'],
                'required'
            ],
            [
                ['user_id','resume_id', 'viewed_at', 'archived_at'],
                'integer'
            ]
        ];
    }
}
