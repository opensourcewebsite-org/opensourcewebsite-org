<?php

declare(strict_types=1);

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 *
 * @property int $id
 * @property int $user_id
 * @property int $order_id
 * @property int $viewed_at
 * @property int $archived_at
 */
class CurrencyExchangeOrderResponse extends ActiveRecord
{
    public static function findOrNewResponse(int $userId, int $modelId): self
    {
        if (!($response = self::findOne(['user_id' => $userId, 'order_id' => $modelId]))) {
            $response = new self(['user_id' => $userId, 'order_id' => $modelId]);
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%currency_exchange_order_response}}';
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
            [['user_id', 'order_id'], 'required'],
            [['user_id', 'order_id', 'viewed_at', 'archived_at'], 'integer'],
        ];
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getOrder(): ActiveQuery
    {
        return $this->hasOne(CurrencyExchangeOrder::class, ['id' => 'order_id']);
    }
}
