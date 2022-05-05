<?php

declare(strict_types=1);

namespace app\widgets\selects;

use app\models\User;
use app\widgets\base\Widget;
use kartik\select2\Select2;
use yii\web\JsExpression;
use Yii;

class ContactSelect extends Widget
{
    private array $defaultOptions = [
        'class' => 'form-control',
        'placeholder' => 'Select...',
    ];

    public array $data = [];

    public array $pluginOptions = [];

    private function getDefaultPluginOptionsAjax (): array
    {
        return [
            'minimumInputLength' => 2,
            'ajax'=>[
                'dataType' => 'json',
                'data' => new JsExpression('function(params) { return {q:params.term,  page: params.page || 1 }; }')
            ],
            'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
            'templateResult' => new JsExpression('function(user) { return user.username; }'),
            'templateSelection' => new JsExpression('function (user) { return user.username; }'),
        ];
    }

    public function run(): string
    {
        if ($this->hasModel()) {
            return Select2::widget([
                'model' => $this->model,
                'attribute' => $this->attribute,
                'data' => $this->data,
                'value' => $this->value,
                'options' => array_merge($this->defaultOptions, $this->options),
                'pluginOptions' =>  array_merge( 
                    !empty($this->pluginOptions['ajax']['url']) ? $this->getdefaultPluginOptionsAjax() :[], 
                    $this->pluginOptions
                ),
            ]);
        } else {
            return Select2::widget([
                'name' => $this->name,
                'data' => $this->data,
                'value' => $this->value,
                'options' => array_merge($this->defaultOptions, $this->options),
                'pluginOptions' => $this->pluginOptions
            ]);
        }
    }

}
