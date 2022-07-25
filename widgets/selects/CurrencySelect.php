<?php

declare(strict_types=1);

namespace app\widgets\selects;

use app\models\Currency;
use app\widgets\base\Widget;
use kartik\select2\Select2;
use Yii;

class CurrencySelect extends Widget
{
    private array $defaultOptions = [
        'class' => 'form-control',
        'placeholder' => 'Select...',
    ];

    public function run(): string
    {
        if ($this->hasModel()) {
            return Select2::widget([
                'model' => $this->model,
                'attribute' =>  $this->attribute,
                'data' => $this->getData(),
                'value' => $this->value,
                'options' => array_merge($this->defaultOptions, $this->options),
            ]);
        } else {
            return Select2::widget([
                'name' => $this->name,
                'data' => $this->getData(),
                'value' => $this->value,
                'options' => array_merge($this->defaultOptions, $this->options),
            ]);
        }
    }

    private function getData(): ?array
    {
        $result = [];

        $items = Currency::find()
            ->orderBy([
                'code' => SORT_ASC,
            ])
            ->asArray()
            ->all();

        foreach ($items as $item) {
            $result[$item['id']] = $item['code'] . ' - ' . Yii::t('app', $item['name']);
        }

        return $result;
    }
}
