<?php

declare(strict_types=1);

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Class AdSearchResponse
 * @package app\models
 *
 * @property int $id
 * @property int $user_id
 * @property int $ad_search_id
 * @property int $viewed_at
 * @property int $archived_at
 */
class AdSearchResponse extends ActiveRecord
{
    public static function findOrNewResponse(int $userId, int $modelId): self
    {
        if (!($response = self::findOne(['user_id' => $userId, 'ad_search_id' => $modelId]))) {
            $response = new self(['user_id' => $userId, 'ad_search_id' => $modelId]);
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%ad_search_response}}';
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['user_id', 'ad_search_id'], 'required'],
            [['user_id', 'ad_search_id', 'viewed_at', 'archived_at'], 'integer'],
        ];
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getAdSearch(): ActiveQuery
    {
        return $this->hasOne(AdSearch::class, ['id' => 'ad_search_id']);
    }
}
