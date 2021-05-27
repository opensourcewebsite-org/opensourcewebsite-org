<?php
declare(strict_types=1);

namespace app\widgets\CurrencySelect;


use app\models\Currency;
use app\widgets\base\Widget;
use kartik\select2\Select2;

class CurrencySelect extends Widget {

    private array $currenciesList = [];

    private array $defaultOptions = [
        'prompt' => ''
    ];

    public function init()
    {
        $this->currenciesList = $this->prepareCurrenciesForSelect();

        parent::init();
    }

    public function run(): string
    {
        if ($this->hasModel()) {
            return Select2::widget([
                'model' => $this->model,
                'attribute' =>  $this->attribute,
                'data' => $this->currenciesList,
                'value' => $this->value,
                'options' => array_merge($this->defaultOptions, $this->options)
            ]);
        }
        return Select2::widget([
            'name' => $this->name,
            'data' => $this->currenciesList,
            'value' => $this->value,
            'options' => array_merge($this->defaultOptions, $this->options),
        ]);
    }

    private function prepareCurrenciesForSelect(): array
    {
        $ret = [];
        foreach (Currency::find()->asArray()->all() as $currency) {
            $ret[$currency['id']] = $currency['code'] . ' - ' . $currency['name'];
        }

        return $ret;
    }

}
