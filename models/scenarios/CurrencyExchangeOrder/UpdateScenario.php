<?php

declare(strict_types=1);

namespace app\models\scenarios\CurrencyExchangeOrder;

use app\models\CurrencyExchangeOrder;
use app\models\matchers\ModelLinker;
use Yii;

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
        if ($this->model->isAttributeChanged('selling_rate')) {
            if (floatval($this->model->selling_rate)) {
                $this->model->buying_rate = 1 / $this->model->selling_rate;
            } else {
                $this->model->selling_rate = null;
                $this->model->buying_rate = null;
            }
        } elseif ($this->model->isAttributeChanged('buying_rate')) {
            if (floatval($this->model->buying_rate)) {
                $this->model->selling_rate = 1 / $this->model->buying_rate;
            } else {
                $this->model->selling_rate = null;
                $this->model->buying_rate = null;
            }
        }

        if ($this->model->isAttributeChanged('status') ||
        $this->model->isAttributeChanged('selling_currency_id') ||
        $this->model->isAttributeChanged('buying_currency_id') ||
        $this->model->isAttributeChanged('selling_rate') ||
        $this->model->isAttributeChanged('buying_rate') ||
        $this->model->isAttributeChanged('selling_currency_min_amount', false) ||
        $this->model->isAttributeChanged('selling_currency_max_amount', false) ||
        $this->model->isAttributeChanged('selling_cash_on', false) ||
        $this->model->isAttributeChanged('selling_location_lat') ||
        $this->model->isAttributeChanged('selling_location_lon') ||
        $this->model->isAttributeChanged('selling_delivery_radius', false) ||
        $this->model->isAttributeChanged('buying_cash_on', false) ||
        $this->model->isAttributeChanged('buying_location_lat') ||
        $this->model->isAttributeChanged('buying_location_lon') ||
        $this->model->isAttributeChanged('buying_delivery_radius', false)
        ) {
            $this->linker->unlinkMatches();

            return true;
        }

        return false;
    }
}
