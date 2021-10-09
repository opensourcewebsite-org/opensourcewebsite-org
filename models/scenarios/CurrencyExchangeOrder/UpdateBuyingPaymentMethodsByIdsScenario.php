<?php

declare(strict_types=1);

namespace app\models\scenarios\CurrencyExchangeOrder;

use app\models\CurrencyExchangeOrder;
use app\models\CurrencyExchangeOrderBuyingPaymentMethod;
use app\components\helpers\ArrayHelper;

class UpdateBuyingPaymentMethodsByIdsScenario
{
    private CurrencyExchangeOrder $model;

    public function __construct(CurrencyExchangeOrder $model)
    {
        $this->model = $model;
    }

    public function run()
    {
        $currentIds = $this->model->getBuyingPaymentMethodIds();

        $toDeleteIds = array_diff($currentIds, $this->model->buyingPaymentMethodIds);
        $toAddIds = array_diff($this->model->buyingPaymentMethodIds, $currentIds);

        if ($toDeleteIds || $toAddIds) {
            $this->model->trigger(CurrencyExchangeOrder::EVENT_BUYING_PAYMENT_METHODS_UPDATED);
        }

        foreach ($toAddIds as $id) {
            (new CurrencyExchangeOrderBuyingPaymentMethod([
                'order_id' => $this->model->id,
                'payment_method_id' => $id,
                ])
            )
            ->save();
        }

        if ($toDeleteIds) {
            CurrencyExchangeOrderBuyingPaymentMethod::deleteAll([
                'and',
                ['order_id' => $this->model->id],
                ['in', 'payment_method_id', $toDeleteIds],
            ]);
        }
    }
}
