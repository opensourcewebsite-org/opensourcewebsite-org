<?php

namespace app\models\FormModels\CurrencyExchange;

use app\models\CurrencyExchangeOrder;
use app\models\CurrencyExchangeOrderBuyingPaymentMethod;
use app\models\CurrencyExchangeOrderSellingPaymentMethod;
use app\models\PaymentMethod;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

class OrderPaymentMethods extends Model {

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
