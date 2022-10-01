<?php

declare(strict_types=1);

namespace app\models;

use yii\behaviors\TimestampBehavior;
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
    public static function findOrNewResponse(int $userId, int $modelId): self
    {
        if (!($response = self::findOne(['user_id' => $userId, 'resume_id' => $modelId]))) {
            $response = new self(['user_id' => $userId, 'resume_id' => $modelId]);
        }

        return $response;
    }

    public static function tableName(): string
    {
        return '{{%job_resume_response}}';
    }

    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'viewed_at',
                'updatedAtAttribute' => false,
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [['user_id', 'resume_id'], 'required'],
            [['user_id', 'resume_id', 'viewed_at', 'archived_at'], 'integer'],
        ];
    }
}
