<?php

declare(strict_types=1);

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $user_id
 * @property int $vacancy_id
 * @property int $viewed_at
 * @property int $archived_at
 */
class JobVacancyResponse extends ActiveRecord
{
    public static function findOrNewResponse(int $userId, int $modelId): self
    {
        if (!($response = self::findOne(['user_id' => $userId, 'vacancy_id' => $modelId]))) {
            $response = new self(['user_id' => $userId, 'vacancy_id' => $modelId]);
        }

        return $response;
    }

    public static function tableName(): string
    {
        return '{{%job_vacancy_response}}';
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
            [['user_id', 'vacancy_id'], 'required'],
            [['user_id', 'vacancy_id', 'viewed_at', 'archived_at'], 'integer'],
        ];
    }
}
