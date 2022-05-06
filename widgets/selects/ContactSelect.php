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
            'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
            'templateResult' => new JsExpression('function(user) { return user.username; }'),
            'templateSelection' => new JsExpression('function (user) { return user.username; }'),
        ];
    }

    private function registerJs(): void
    {
        $this->getView()->registerJs(new JsExpression("
            $('#{$this->getId()}').on('select2:select', function (e) {
                $('#{$this->getId()}').trigger('change.select2');
            });
        "));  
    }

    public function init()
    {
        parent::init();
    }

    public function run(): string
    {

        if(!empty($this->pluginOptions['ajax'])) {
            $this->registerJs();
        }

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
