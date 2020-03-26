<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "payment_method_currency".
 *
 * @property int $id
 * @property int $payment_method_id
 * @property int $currency_id
 */
class PaymentMethodCurrency extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'payment_method_currency';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['payment_method_id', 'currency_id'], 'required'],
            [['payment_method_id', 'currency_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'payment_method_id' => 'Payment Method ID',
            'currency_id' => 'Currency ID',
        ];
    }
}
