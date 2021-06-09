<?php

namespace app\models\FormModels\CurrencyExchange;

use Yii;
use yii\base\Model;
use app\models\CurrencyExchangeOrder;

class OrderPaymentMethods extends Model
{
    public $sellingPaymentMethods = [];
    public $buyingPaymentMethods = [];

    private CurrencyExchangeOrder $_order;

    /**
     * {@inheritDoc}
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->buyingPaymentMethods = $this->_order->getCurrentBuyingPaymentMethodsIds();
        $this->sellingPaymentMethods = $this->_order->getCurrentSellingPaymentMethodsIds();
    }

    /**
     * {@inheritDoc}
     */
    public function rules(): array
    {
        return [
            [
                ['sellingPaymentMethods', 'buyingPaymentMethods'],
                'filter', 'filter' => function ($value) {
                    return is_array($value) ? array_map('intval', $value): [];
                }
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function attributeLabels(): array
    {
        return [
            'sellingPaymentMethods' => Yii::t('app', 'Payment methods for Buy'),
            'buyingPaymentMethods' => Yii::t('app', 'Payment methods for Sell'),
        ];
    }

    public function setOrder(CurrencyExchangeOrder $order): void
    {
        $this->_order = $order;
    }

    public function getOrder(): CurrencyExchangeOrder
    {
        return $this->_order;
    }
}
