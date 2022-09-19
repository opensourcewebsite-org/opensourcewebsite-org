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
class PaymentMethodCurrencyByCurrency extends PaymentMethodCurrency
{
    public $name;
    /**
     * {@inheritdoc}
     */
    public static function find($params=null)
    {   
        if (isset($params)) {
            $params = $params[0];
            if ($params->getIntermediateField('currencyexchangeorderattributeName') == 'sellingPaymentMethods') {
                $currency = $params->getIntermediateField('currencyexchangeorderselling_currency_id');
            }
            elseif ($params->getIntermediateField('currencyexchangeorderattributeName') == 'buyingPaymentMethods') {
                $currency = $params->getIntermediateField('currencyexchangeorderbuying_currency_id');
            }
            return parent::find()->select('payment_method_id, name')->innerJoin('payment_method', 'payment_method.id = payment_method_id')->andWhere(['=', 'currency_id', $currency]);
        }
        else {
            return parent::find();
        }
    }

    public function getLabel(): string
    {
        return $this->name;
    }
}
