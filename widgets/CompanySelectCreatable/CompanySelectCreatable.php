<?php
declare(strict_types=1);

namespace app\widgets\CompanySelectCreatable;

use yii\base\Widget;
use kartik\select2\Select2Asset;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\ActiveField;

class CompanySelectCreatable extends Widget {

    public ?ActiveField $field = null;

    public ?Model $model = null;

    public ?string $attribute = null;

    public ?string $name = null;

    public array $value = [];

    public array $options = [];

    public array $companies = [];

    private array $defaultOptions = ['class' => 'form-control flex-grow-1', 'placeholder' => 'Select Company...'];


    public function init(){

        if ($this->name === null && !$this->hasModel()) {
            throw new InvalidConfigException("Either 'name', or 'model' and 'attribute' properties must be specified.");
        }
        if (!isset($this->options['id'])) {
            $this->id = $this->options['id'] = $this->hasModel() ? Html::getInputId($this->model, $this->attribute) : $this->getId();
        }
        parent::init();
    }

    public function run(): string
    {

        return $this->render('control', [
            'hasModel' => $this->hasModel(),
            'model' => $this->model,
            'name'=> $this->name,
            'attribute' => $this->attribute,
            'companies' => $this->companies,
            'options' => array_merge($this->defaultOptions, $this->options),
            'value' => $this->value,
            'controlId' => $this->id
        ] );
    }

    protected function hasModel(): bool
    {
        return $this->model instanceof Model && $this->attribute !== null;
    }

    private function registerJs(){
        $keywordCreateUrl = Url::to('/company-user/create-ajax');

        $this->getView()->registerJs(new JsExpression("

        "));
    }

    private function registerAssets()
    {
        Select2Asset::register($this->getView());
    }
}
