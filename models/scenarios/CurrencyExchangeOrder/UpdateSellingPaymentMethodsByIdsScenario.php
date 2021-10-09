<?php

declare(strict_types=1);

namespace app\models\scenarios\CurrencyExchangeOrder;

use app\models\CurrencyExchangeOrder;
use app\models\CurrencyExchangeOrderSellingPaymentMethod;
use app\components\helpers\ArrayHelper;

class UpdateSellingPaymentMethodsByIdsScenario
{
    private CurrencyExchangeOrder $model;

    public function __construct(CurrencyExchangeOrder $model)
    {
        $this->model = $model;
    }

    public function run()
    {
        $currentIds = $this->model->getSellingPaymentMethodIds();

        $toDeleteIds = array_diff($currentIds, $this->model->sellingPaymentMethodIds);
        $toAddIds = array_diff($this->model->sellingPaymentMethodIds, $currentIds);

        if ($toDeleteIds || $toAddIds) {
            $this->model->trigger(CurrencyExchangeOrder::EVENT_SELLING_PAYMENT_METHODS_UPDATED);
        }

        foreach ($toAddIds as $id) {
            (new CurrencyExchangeOrderSellingPaymentMethod([
                'order_id' => $this->model->id,
                'payment_method_id' => $id,
                ])
            )
            ->save();
        }

        if ($toDeleteIds) {
            CurrencyExchangeOrderSellingPaymentMethod::deleteAll([
                'and',
                ['order_id' => $this->model->id],
                ['in', 'payment_method_id', $toDeleteIds],
            ]);
        }
    }
}
