<?php

declare(strict_types=1);

namespace app\models\scenarios\CurrencyExchangeOrder;

use app\models\CurrencyExchangeOrder;
use app\models\scenarios\traits\ValidateRatingTrait;
use Yii;

final class SetActiveScenario
{
    use ValidateRatingTrait;

    private CurrencyExchangeOrder $model;

    private $modelClass = 'CurrencyExchangeOrder';

    private array $errors = [];

    public function __construct(CurrencyExchangeOrder $model)
    {
        $this->model = $model;
    }

    public function run(): bool
    {
        if ($this->validateRating() && $this->validateSellingPaymentMethods() && $this->validateBuyingPaymentMethods()) {
            $this->model->setActive();

            return true;
        }

        return false;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getFirstError(): string
    {
        return $this->errors ? array_shift($this->errors) : '';
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
