<?php

namespace app\services;

use app\models\CurrencyExchangeOrder;
use app\models\CurrencyExchangeOrderBuyingPaymentMethod;
use app\models\CurrencyExchangeOrderSellingPaymentMethod;
use app\models\PaymentMethod;

class CurrencyExchangeService
{
    /**
     * Update Payment Methods for [[CurrencyExchangeOrder]]
     * if either buying or selling payment methods updated, it clear matched offers for this [[CurrencyExchangeOrder]] model
     * @param CurrencyExchangeOrder $order
     * @param array $sellPaymentMethods
     * @param array $buyPaymentMethods
     */
    public function updatePaymentMethods(CurrencyExchangeOrder $order, array $sellPaymentMethods = [], array $buyPaymentMethods = []): void
    {

        $sellUpdated = $sellPaymentMethods ? $this->updateSellingPaymentMethods($order, $sellPaymentMethods): false;
        $buyUpdated = $buyPaymentMethods ? $this->updateBuyingPaymentMethods($order, $buyPaymentMethods): false;

        if ($sellUpdated || $buyUpdated) {
            $order->clearMatches();
        }
    }

    /**
     * Update CurrencyExchangeOrder model payment methods to sell
     * @param CurrencyExchangeOrder $order
     * @param array $newMethodsIds
     * @return bool
     */
    public function updateSellingPaymentMethods(CurrencyExchangeOrder $order, array $newMethodsIds): bool
    {

        [$toDelete, $toLink] = $this->getToDeleteAndToLinkIds(
            $order->getCurrentSellingPaymentMethodsIds(),
            $newMethodsIds
        );
        if ($toDelete) {
            CurrencyExchangeOrderSellingPaymentMethod::deleteAll(['AND', ['order_id' => $order->id], ['in', 'payment_method_id', $toDelete]]);
        }
        foreach ($toLink as $id) {
            $order->link('sellingPaymentMethods', PaymentMethod::findOne($id));
        }
        return (!!$toDelete || !!$toLink);

    }

    /**
     * Update CurrencyExchangeOrder model payment methods to buy
     * @param CurrencyExchangeOrder $order
     * @param array $newMethodsIds
     * @return bool
     */
    public function updateBuyingPaymentMethods(CurrencyExchangeOrder $order, array $newMethodsIds): bool
    {

        [$toDelete, $toLink] = $this->getToDeleteAndToLinkIds(
            $order->getCurrentBuyingPaymentMethodsIds(),
            $newMethodsIds
        );

        if ($toDelete) {
            CurrencyExchangeOrderBuyingPaymentMethod::deleteAll(['AND', ['order_id' => $order->id], ['in', 'payment_method_id', $toDelete]]);
        }

        foreach ($toLink as $id) {
            $order->link('buyingPaymentMethods', PaymentMethod::findOne($id));
        }
        return (!!$toDelete || !!$toLink);
    }

    private function getToDeleteAndToLinkIds(array $currentMethodsIds, array $newMethodsIds): array
    {
        $toDelete = array_values(array_diff($currentMethodsIds, $newMethodsIds));
        $toLink = array_values(array_diff($newMethodsIds, $currentMethodsIds));

        return [$toDelete, $toLink];
    }

}
