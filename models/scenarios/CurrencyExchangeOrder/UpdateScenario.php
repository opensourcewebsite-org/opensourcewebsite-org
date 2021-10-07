<?php

declare(strict_types=1);

namespace app\models\scenarios\CurrencyExchangeOrder;

use app\models\matchers\ModelLinker;
use app\models\CurrencyExchangeOrder;

class UpdateScenario
{
    private CurrencyExchangeOrder $model;
    private ModelLinker $linker;

    public function __construct(CurrencyExchangeOrder $model)
    {
        $this->model = $model;
        $this->linker = new ModelLinker($this->model);
    }

    public function run(): bool
    {
        if ($this->model->isAttributeChanged('status') ||
        $this->model->isAttributeChanged('selling_currency_id') ||
        $this->model->isAttributeChanged('buying_currency_id') ||
        $this->model->isAttributeChanged('fee', false) ||
        $this->model->isAttributeChanged('selling_currency_min_amount', false) ||
        $this->model->isAttributeChanged('selling_currency_max_amount', false) ||
        $this->model->isAttributeChanged('selling_cash_on') ||
        $this->model->isAttributeChanged('selling_location_lat') ||
        $this->model->isAttributeChanged('selling_location_lon') ||
        $this->model->isAttributeChanged('selling_delivery_radius') ||
        $this->model->isAttributeChanged('buying_cash_on') ||
        $this->model->isAttributeChanged('buying_location_lat') ||
        $this->model->isAttributeChanged('buying_location_lon') ||
        $this->model->isAttributeChanged('buying_delivery_radius')
        ) {
            $this->linker->unlinkMatches();

            return true;
        }

        return false;
    }
}
