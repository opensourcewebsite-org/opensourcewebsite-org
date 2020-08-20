<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "currency_exchange_order_selling_payment_method".
 *
 * @property int $id
 * @property int $order_id
 * @property int $payment_method_id
 *
 * @property CurrencyExchangeOrder $order
 * @property PaymentMethod $paymentMethod
 */
class CurrencyExchangeOrderSellingPaymentMethod extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'currency_exchange_order_selling_payment_method';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'payment_method_id'], 'required'],
            [['order_id', 'payment_method_id'], 'integer'],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => CurrencyExchangeOrder::className(), 'targetAttribute' => ['order_id' => 'id']],
            [['payment_method_id'], 'exist', 'skipOnError' => true, 'targetClass' => PaymentMethod::className(), 'targetAttribute' => ['payment_method_id' => 'id']],
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
            'payment_method_id' => 'Payment Method ID',
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
     * Gets query for [[PaymentMethod]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentMethod()
    {
        return $this->hasOne(PaymentMethod::className(), ['id' => 'payment_method_id']);
    }
}
