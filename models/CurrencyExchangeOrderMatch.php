<?php

namespace app\models;

use Yii;

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
class CurrencyExchangeOrderMatch extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'currency_exchange_order_match';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'match_order_id'], 'required'],
            [['order_id', 'match_order_id'], 'integer'],
            [['match_order_id'], 'exist', 'skipOnError' => true, 'targetClass' => CurrencyExchangeOrder::className(), 'targetAttribute' => ['match_order_id' => 'id']],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => CurrencyExchangeOrder::className(), 'targetAttribute' => ['order_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_id' => 'Order ID',
            'match_order_id' => 'Match Order ID',
        ];
    }

    /**
     * Gets query for [[Order]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(CurrencyExchangeOrder::className(), ['id' => 'order_id']);
    }

    /**
     * Gets query for [[MatchOrder]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMatchOrder()
    {
        return $this->hasOne(CurrencyExchangeOrder::className(), ['id' => 'match_order_id']);
    }
}
