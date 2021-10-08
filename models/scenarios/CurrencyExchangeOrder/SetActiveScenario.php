<?php

declare(strict_types=1);

namespace app\models\scenarios\CurrencyExchangeOrder;

use Yii;
use app\models\CurrencyExchangeOrder;

final class SetActiveScenario
{
    private CurrencyExchangeOrder $model;
    private array $errors = [];

    public function __construct(CurrencyExchangeOrder $model)
    {
        $this->model = $model;
    }

    public function run(): bool
    {
        if ($this->validateSellingPaymentMethods() && $this->validateBuyingPaymentMethods()) {
            $this->model->setActive();

            return true;
        }

        return false;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    private function validateSellingPaymentMethods(): bool
    {
        if (!$this->model->selling_cash_on && !$this->model->sellingPaymentMethods) {
            $this->errors['sellingPaymentMethods'] = Yii::t('app', 'At least one selling payment method should be set') . '.';


            return false;
        }

        return true;
    }

    private function validateBuyingPaymentMethods(): bool
    {
        if (!$this->model->buying_cash_on && !$this->model->buyingPaymentMethods) {
            $this->errors['buyingPaymentMethods'] = Yii::t('app', 'At least one buying payment method should be set') . '.';

            return false;
        }

        return true;
    }
}
