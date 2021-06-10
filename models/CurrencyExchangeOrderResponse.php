<?php
declare(strict_types=1);

namespace app\models;

use yii\behaviors\TimestampBehavior;
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
    public static function tableName(): string
    {
        return "currency_exchange_order_response";
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
            [
                ['user_id','order_id'],
                'required'
            ],
            [
                ['user_id','order_id', 'viewed_at', 'archived_at'],
                'integer'
            ]
        ];
    }
}
