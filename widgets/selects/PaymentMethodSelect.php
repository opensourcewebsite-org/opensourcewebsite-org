<?php

declare(strict_types=1);

namespace app\widgets\selects;

use app\components\helpers\ArrayHelper;
use app\models\PaymentMethod;
use app\models\PaymentMethodCurrency;
use app\widgets\base\Widget;
use kartik\select2\Select2;
use yii\base\Model;
use yii\web\JsExpression;
use yii\helpers\Url;
use Yii;

class PaymentMethodSelect extends Widget
{
    private array $defaultOptions = [
        'class' => 'form-control',
        'multiple' => true,
        'placeholder' => 'Select...',
    ];

    private array $pluginOptions = [];

    public $currencyId = null;

    public function init()
    {
        $this->pluginOptions = $this->preparePluginOptions();

        parent::init();
    }

    public function run(): string
    {
        if ($this->hasModel()) {
            return Select2::widget([
                'model' => $this->model,
                'attribute' => $this->attribute,
                'data' => $this->getData(),
                'showToggleAll' => false,
                'options' => array_merge($this->defaultOptions, $this->options),
                'pluginOptions' => $this->pluginOptions,
            ]);
        } else {
            return Select2::widget([
                'name' => $this->name,
                'data' => $this->getData(),
                'showToggleAll' => false,
                'value' => $this->value,
                'options' => array_merge($this->defaultOptions, $this->options),
                'pluginOptions' => $this->pluginOptions,
            ]);
        }
    }

    protected function hasModel(): bool
    {
        return $this->model instanceof Model && $this->attribute !== null;
    }

    private function getData(): array
    {
        if ($this->currencyId) {
            $query = PaymentMethod::find()
                ->andWhere([
                    'in',
                    'id',
                    PaymentMethodCurrency::find()
                        ->select('payment_method_id')
                        ->andWhere([
                            'currency_id' => $this->currencyId,
                        ]),
                ])
                ->orderBy([
                    'name' => SORT_ASC,
                ]);
        } else {
            $query = PaymentMethod::find()
                ->orderBy([
                    'name' => SORT_ASC,
                ]);
        }

        return ArrayHelper::map($query->asArray()->all(), 'id', 'name');
    }

    private function preparePluginOptions(): array
    {
        return [
            'tags' => true,
            'allowClear' => true,
            'createTag' => new JsExpression("function(tag) {
                            return {
                                id: tag.term,
                                text: tag.term,
                                newTag: true
                            };
                        }
                    "),
        ];
    }

    private function getPaymentMethodsForCurrencyId(int $currencyId): array
    {
        return PaymentMethod::find()->joinWith('currencies')
            ->where(['currency.id' => $currencyId])
            ->all();
    }
}
