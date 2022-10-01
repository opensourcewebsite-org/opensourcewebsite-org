<?php

namespace app\models;

use app\models\queries\CurrencyExchangeOrderMatchQuery;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "currency_exchange_order_match".
 *
 * @property int $id
 * @property int $order_id
 * @property int $match_order_id
 *
 * @property CurrencyExchangeOrder $order
 * @property CurrencyExchangeOrder $matchOrder
 */
class CurrencyExchangeOrderMatch extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%currency_exchange_order_match}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'match_order_id'], 'required'],
            [['order_id', 'match_order_id'], 'integer'],
            [['match_order_id'], 'exist', 'skipOnError' => true, 'targetClass' => CurrencyExchangeOrder::class, 'targetAttribute' => ['match_order_id' => 'id']],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => CurrencyExchangeOrder::class, 'targetAttribute' => ['order_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'order_id' => 'Order ID',
            'match_order_id' => 'Match Order ID',
        ];
    }

    public static function find(): CurrencyExchangeOrderMatchQuery
    {
        return new CurrencyExchangeOrderMatchQuery(get_called_class());
    }

    public function getOrder(): ActiveQuery
    {
        return $this->hasOne(CurrencyExchangeOrder::class, ['id' => 'order_id']);
    }

    public function getMatchOrder(): ActiveQuery
    {
        return $this->hasOne(CurrencyExchangeOrder::class, ['id' => 'match_order_id']);
    }

    public function isNew()
    {
        return !CurrencyExchangeOrderResponse::find()
            ->andWhere([
                'user_id' => $this->order->user_id,
                'order_id' => $this->match_order_id,
            ])
            ->andWhere([
                'is not', 'viewed_at', null,
            ])
            ->exists();
    }
}
